<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\MidtransService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService
    ) {}

    /**
     * Midtrans Webhook Notification Callback.
     * Validates SHA512 signature, idempotent.
     */
    public function handleWebhook(Request $request, string $provider = 'midtrans'): JsonResponse
    {
        $payload = $request->all();

        try {
            $result = $this->midtransService->handleWebhook($payload);

            return response()->json([
                'status' => 'success',
                'message' => $result['message'],
            ], 200);
        } catch (Exception $e) {
            $statusCode = ($e->getMessage() === 'Invalid signature key.') ? 401 : 400;

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Helper endpoint for Sandbox testing / automated test suites:
     * Generates a valid Midtrans webhook payload with exact SHA512 signature
     * and processes it directly.
     */
    public function simulate(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => ['required', 'string'],
            'transaction_status' => ['required', 'string', 'in:settlement,capture,expire,cancel,deny'],
            'payment_type' => ['nullable', 'string'],
        ]);

        $payment = Payment::where('order_id', $request->order_id)->firstOrFail();
        $serverKey = config('midtrans.server_key');

        $statusCode = '200';
        $grossAmount = number_format($payment->amount, 2, '.', '');
        $signature = hash('sha512', $payment->order_id . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id' => $payment->order_id,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => $request->transaction_status,
            'fraud_status' => 'accept',
            'payment_type' => $request->input('payment_type', 'qris'),
            'transaction_id' => 'midtrans-trx-' . time(),
            'transaction_time' => now()->toDateTimeString(),
        ];

        try {
            $result = $this->midtransService->handleWebhook($payload);

            return response()->json([
                'status' => 'success',
                'message' => 'Simulasi pembayaran sandbox berhasil diproses.',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
