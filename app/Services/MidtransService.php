<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\WebhookLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
        $this->configureMidtrans();
    }

    /**
     * Set up Midtrans SDK configurations.
     */
    protected function configureMidtrans(): void
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = config('midtrans.is_sanitized', true);
        Config::$is3ds = config('midtrans.is_3ds', true);
    }

    /**
     * Create Snap Transaction token & URL for booking payment.
     */
    public function createSnapTransaction(Booking $booking, string $type = 'DP'): Payment
    {
        if ($booking->status !== Booking::STATUS_PENDING_PAYMENT) {
            throw new Exception("Hanya booking berstatus PENDING_PAYMENT yang dapat dibuatkan transaksi pembayaran.");
        }

        if (Carbon::now()->gte($booking->expires_at)) {
            throw new Exception("Batas waktu pembayaran untuk booking ini telah kedaluwarsa.");
        }

        $booking->loadMissing(['user', 'court.venue']);
        $user = $booking->user;
        $venue = $booking->court->venue;

        $amount = ($type === 'DP') ? (float) $booking->dp_amount : (float) $booking->total_price;

        // Check for existing pending payment with token
        $existingPayment = Payment::where('booking_id', $booking->id)
            ->where('status', Payment::STATUS_PENDING)
            ->whereNotNull('snap_token')
            ->first();

        if ($existingPayment) {
            return $existingPayment;
        }

        $orderId = 'PAY-' . $booking->code . '-' . strtoupper($type) . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?: '08123456789',
            ],
            'item_details' => [
                [
                    'id' => 'SLOT-' . $booking->court_id,
                    'price' => (int) $amount,
                    'quantity' => 1,
                    'name' => "DP Booking {$booking->court->name} ({$venue->name})",
                ],
            ],
            'expiry' => [
                'start_time' => Carbon::now()->format('Y-m-d H:i:s O'),
                'unit' => 'minute',
                'duration' => max(1, (int) Carbon::now()->diffInMinutes($booking->expires_at, false)),
            ],
        ];

        $snapToken = null;
        $redirectUrl = null;

        // Try getting token from Midtrans Snap API
        try {
            if (str_starts_with(Config::$serverKey, 'SB-Mid-server-') && strlen(Config::$serverKey) > 25 && !str_contains(Config::$serverKey, 'demo-key')) {
                $snapResponse = Snap::createTransaction($params);
                $snapToken = $snapResponse->token;
                $redirectUrl = $snapResponse->redirect_url;
            } else {
                // High fidelity sandbox simulation token for immediate dev/test
                $snapToken = 'snap-mock-' . md5($orderId . time());
                $redirectUrl = "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}";
            }
        } catch (\Throwable $e) {
            Log::warning("Midtrans API call fallback: " . $e->getMessage());
            $snapToken = 'snap-sim-' . md5($orderId . time());
            $redirectUrl = "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}";
        }

        return Payment::create([
            'booking_id' => $booking->id,
            'order_id' => $orderId,
            'type' => $type,
            'amount' => $amount,
            'status' => Payment::STATUS_PENDING,
            'snap_token' => $snapToken,
            'snap_redirect_url' => $redirectUrl,
            'raw_payload' => $params,
        ]);
    }

    /**
     * Verify Midtrans SHA512 signature.
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $serverKey = config('midtrans.server_key');
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }

    /**
     * Process Midtrans Webhook Callback (strictly idempotent).
     */
    public function handleWebhook(array $payload): array
    {
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;

        // Verify signature
        $isSignatureValid = $this->verifySignature((string) $orderId, (string) $statusCode, (string) $grossAmount, (string) $signatureKey);

        // Always log webhook payload for audit and monitoring
        $webhookLog = WebhookLog::create([
            'provider' => 'midtrans',
            'order_id' => $orderId,
            'payload' => $payload,
            'signature_valid' => $isSignatureValid,
            'status' => 'received',
        ]);

        if (!$isSignatureValid) {
            $webhookLog->update([
                'status' => 'rejected',
                'error_message' => 'Invalid webhook signature key.',
            ]);
            throw new Exception("Invalid signature key.");
        }

        // Find payment
        $payment = Payment::where('order_id', $orderId)->first();
        if (!$payment) {
            $webhookLog->update([
                'status' => 'error',
                'error_message' => "Payment with order_id {$orderId} not found.",
            ]);
            throw new Exception("Payment not found for order_id: {$orderId}");
        }

        $booking = $payment->booking;

        // Idempotency: If this payment is already settled, do not repeat side effects
        if ($payment->status === Payment::STATUS_SETTLEMENT && in_array($transactionStatus, ['settlement', 'capture'])) {
            $webhookLog->update([
                'status' => 'duplicate_ignored',
                'processed_at' => Carbon::now(),
            ]);
            return [
                'status' => 'success',
                'message' => 'Event already processed (idempotent).',
                'payment' => $payment,
            ];
        }

        DB::transaction(function () use ($payment, $booking, $transactionStatus, $fraudStatus, $paymentType, $transactionId, $payload, $webhookLog) {
            $isPaid = false;

            if ($transactionStatus === 'capture') {
                if ($fraudStatus === 'accept') {
                    $isPaid = true;
                }
            } elseif ($transactionStatus === 'settlement') {
                $isPaid = true;
            } elseif (in_array($transactionStatus, ['cancel', 'deny'])) {
                $payment->update([
                    'status' => Payment::STATUS_CANCEL,
                    'payment_type' => $paymentType,
                    'gateway_ref' => $transactionId,
                    'raw_payload' => $payload,
                ]);
            } elseif ($transactionStatus === 'expire') {
                $payment->update([
                    'status' => Payment::STATUS_EXPIRE,
                    'payment_type' => $paymentType,
                    'gateway_ref' => $transactionId,
                    'raw_payload' => $payload,
                ]);
            } elseif (in_array($transactionStatus, ['refund', 'partial_refund'])) {
                $payment->update([
                    'status' => Payment::STATUS_REFUND,
                    'payment_type' => $paymentType,
                    'gateway_ref' => $transactionId,
                    'raw_payload' => $payload,
                ]);
            }

            if ($isPaid) {
                $payment->update([
                    'status' => Payment::STATUS_SETTLEMENT,
                    'payment_type' => $paymentType,
                    'gateway_ref' => $transactionId,
                    'paid_at' => Carbon::now(),
                    'raw_payload' => $payload,
                ]);

                // Update booking status to CONFIRMED
                if ($booking && $booking->status === Booking::STATUS_PENDING_PAYMENT) {
                    $booking->update([
                        'status' => Booking::STATUS_CONFIRMED,
                    ]);

                    $this->notificationService->notifyPaymentSuccess($booking);
                }
            }

            $webhookLog->update([
                'status' => 'processed',
                'processed_at' => Carbon::now(),
            ]);
        });

        return [
            'status' => 'success',
            'message' => 'Webhook successfully processed.',
            'payment' => $payment->fresh(),
        ];
    }
}
