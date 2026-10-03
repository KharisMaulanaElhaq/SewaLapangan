<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\Payment;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Venue $venue;
    protected Court $court;
    protected Booking $booking;
    protected Payment $payment;
    protected string $serverKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serverKey = config('midtrans.server_key');

        $this->user = User::create([
            'name' => 'Tester Penyewa',
            'email' => 'tester@example.com',
            'phone' => '08123456789',
            'password' => bcrypt('password123'),
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $this->venue = Venue::create([
            'owner_id' => $this->user->id,
            'name' => 'Venue Test',
            'slug' => 'venue-test',
            'address' => 'Jl. Test',
            'city' => 'Jakarta',
            'phone' => '0812345678',
            'dp_percent' => 30,
            'cancel_free_hours' => 24,
            'cancel_refund_percent' => 100,
        ]);

        $this->court = Court::create([
            'venue_id' => $this->venue->id,
            'name' => 'Court Test',
            'sport_type' => 'futsal',
            'is_active' => true,
        ]);

        $this->booking = Booking::create([
            'code' => 'BK-TEST-123',
            'user_id' => $this->user->id,
            'court_id' => $this->court->id,
            'total_price' => 200000,
            'dp_amount' => 60000,
            'status' => Booking::STATUS_PENDING_PAYMENT,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);

        $this->payment = Payment::create([
            'booking_id' => $this->booking->id,
            'order_id' => 'PAY-BK-TEST-123-DP',
            'type' => 'DP',
            'amount' => 60000,
            'status' => Payment::STATUS_PENDING,
            'snap_token' => 'mock-token-test',
        ]);
    }

    /**
     * Test valid webhook callback signature transitions booking to CONFIRMED.
     */
    public function test_valid_webhook_signature_confirms_booking(): void
    {
        $statusCode = '200';
        $grossAmount = '60000.00';
        $signature = hash('sha512', $this->payment->order_id . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id' => $this->payment->order_id,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'payment_type' => 'qris',
            'transaction_id' => 'midtrans-trx-999',
        ];

        $response = $this->postJson('/api/v1/payments/webhook/midtrans', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $this->payment->refresh();
        $this->booking->refresh();

        $this->assertEquals(Payment::STATUS_SETTLEMENT, $this->payment->status);
        $this->assertEquals(Booking::STATUS_CONFIRMED, $this->booking->status);
        $this->assertNotNull($this->payment->paid_at);

        $this->assertDatabaseHas('webhook_logs', [
            'order_id' => $this->payment->order_id,
            'signature_valid' => true,
            'status' => 'processed',
        ]);
    }

    /**
     * Test invalid signature is rejected with HTTP 401.
     */
    public function test_invalid_signature_is_rejected(): void
    {
        $payload = [
            'order_id' => $this->payment->order_id,
            'status_code' => '200',
            'gross_amount' => '60000.00',
            'signature_key' => 'FORGED_INVALID_SIGNATURE_KEY',
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson('/api/v1/payments/webhook/midtrans', $payload);

        $response->assertStatus(401);

        $this->assertDatabaseHas('webhook_logs', [
            'order_id' => $this->payment->order_id,
            'signature_valid' => false,
            'status' => 'rejected',
        ]);
    }

    /**
     * Test webhook idempotency: sending duplicate settlement doesn't duplicate side effects.
     */
    public function test_duplicate_webhook_is_idempotent(): void
    {
        $statusCode = '200';
        $grossAmount = '60000.00';
        $signature = hash('sha512', $this->payment->order_id . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id' => $this->payment->order_id,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'payment_type' => 'qris',
            'transaction_id' => 'midtrans-trx-999',
        ];

        // 1st delivery
        $this->postJson('/api/v1/payments/webhook/midtrans', $payload)->assertStatus(200);

        // 2nd delivery (duplicate event)
        $response = $this->postJson('/api/v1/payments/webhook/midtrans', $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('webhook_logs', [
            'status' => 'duplicate_ignored',
        ]);
    }

    /**
     * Test simulation helper endpoint.
     */
    public function test_sandbox_simulate_endpoint(): void
    {
        $response = $this->postJson('/api/v1/payments/simulate', [
            'order_id' => $this->payment->order_id,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $this->assertEquals(Booking::STATUS_CONFIRMED, $this->booking->fresh()->status);
    }
}
