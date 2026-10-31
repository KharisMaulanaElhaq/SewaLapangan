<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\User;
use App\Models\Venue;
use App\Models\WebhookLog;
use App\Services\MidtransService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentFinancialSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected Venue $venue;
    protected Court $court1;
    protected Court $court2;
    protected User $user;
    protected MidtransService $midtransService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->midtransService = app(MidtransService::class);

        $this->user = User::create([
            'name' => 'Kharis Tester',
            'email' => 'kharis.tester@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $owner = User::create([
            'name' => 'Owner Venue',
            'email' => 'owner.summary@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'pengelola',
            'status' => 'active',
        ]);

        $this->venue = Venue::create([
            'owner_id' => $owner->id,
            'name' => 'Champions Court Club',
            'slug' => 'champions-court-club',
            'address' => 'Jl. Juanda No. 1',
            'city' => 'Jakarta',
            'phone' => '081234567890',
            'dp_percent' => 30,
        ]);

        $this->court1 = Court::create([
            'venue_id' => $this->venue->id,
            'name' => 'Court A (Vinyl)',
            'sport_type' => 'futsal',
            'is_active' => true,
        ]);

        $this->court2 = Court::create([
            'venue_id' => $this->venue->id,
            'name' => 'Court B (Parquet)',
            'sport_type' => 'basketball',
            'is_active' => true,
        ]);
    }

    /**
     * Test getFinancialSummary without court filter returns all aggregated data.
     */
    public function test_financial_summary_without_court_filter(): void
    {
        // Court 1 Bookings & Payments
        $booking1 = Booking::create([
            'code' => 'BK-C1-01',
            'user_id' => $this->user->id,
            'court_id' => $this->court1->id,
            'total_price' => 100000,
            'dp_amount' => 50000,
            'status' => Booking::STATUS_CONFIRMED,
            'expires_at' => Carbon::now()->addHour(),
        ]);
        Payment::create([
            'booking_id' => $booking1->id,
            'order_id' => 'PAY-C1-01-DP',
            'type' => Payment::TYPE_DP,
            'amount' => 50000,
            'status' => Payment::STATUS_SETTLEMENT,
        ]);

        $booking2 = Booking::create([
            'code' => 'BK-C1-02',
            'user_id' => $this->user->id,
            'court_id' => $this->court1->id,
            'total_price' => 120000,
            'dp_amount' => 120000,
            'status' => Booking::STATUS_CONFIRMED,
            'expires_at' => Carbon::now()->addHour(),
        ]);
        Payment::create([
            'booking_id' => $booking2->id,
            'order_id' => 'PAY-C1-02-LUNAS',
            'type' => Payment::TYPE_LUNAS,
            'amount' => 120000,
            'status' => Payment::STATUS_SETTLEMENT,
        ]);

        // Court 2 Bookings & Payments
        $booking3 = Booking::create([
            'code' => 'BK-C2-01',
            'user_id' => $this->user->id,
            'court_id' => $this->court2->id,
            'total_price' => 80000,
            'dp_amount' => 30000,
            'status' => Booking::STATUS_PENDING_PAYMENT,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);
        Payment::create([
            'booking_id' => $booking3->id,
            'order_id' => 'PAY-C2-01-PENDING',
            'type' => Payment::TYPE_DP,
            'amount' => 30000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $booking4 = Booking::create([
            'code' => 'BK-C2-02',
            'user_id' => $this->user->id,
            'court_id' => $this->court2->id,
            'total_price' => 80000,
            'dp_amount' => 30000,
            'status' => Booking::STATUS_EXPIRED,
            'expires_at' => Carbon::now()->subMinutes(10),
        ]);
        Payment::create([
            'booking_id' => $booking4->id,
            'order_id' => 'PAY-C2-02-EXPIRE',
            'type' => Payment::TYPE_DP,
            'amount' => 30000,
            'status' => Payment::STATUS_EXPIRE,
        ]);

        $booking5 = Booking::create([
            'code' => 'BK-C1-03',
            'user_id' => $this->user->id,
            'court_id' => $this->court1->id,
            'total_price' => 100000,
            'dp_amount' => 40000,
            'status' => Booking::STATUS_CANCELLED,
            'expires_at' => Carbon::now()->addHour(),
        ]);
        Payment::create([
            'booking_id' => $booking5->id,
            'order_id' => 'PAY-C1-03-CANCEL',
            'type' => Payment::TYPE_DP,
            'amount' => 40000,
            'status' => Payment::STATUS_CANCEL,
        ]);

        $booking6 = Booking::create([
            'code' => 'BK-C2-03',
            'user_id' => $this->user->id,
            'court_id' => $this->court2->id,
            'total_price' => 100000,
            'dp_amount' => 35000,
            'status' => Booking::STATUS_CANCELLED,
            'expires_at' => Carbon::now()->addHour(),
        ]);
        Payment::create([
            'booking_id' => $booking6->id,
            'order_id' => 'PAY-C2-03-REFUND',
            'type' => Payment::TYPE_DP,
            'amount' => 35000,
            'status' => Payment::STATUS_REFUND,
        ]);

        // Webhook logs
        WebhookLog::create([
            'provider' => 'midtrans',
            'order_id' => 'PAY-C1-01-DP',
            'payload' => ['order_id' => 'PAY-C1-01-DP'],
            'signature_valid' => true,
            'status' => WebhookLog::STATUS_PROCESSED,
        ]);
        WebhookLog::create([
            'provider' => 'midtrans',
            'order_id' => 'PAY-C1-01-DP',
            'payload' => ['order_id' => 'PAY-C1-01-DP'],
            'signature_valid' => true,
            'status' => WebhookLog::STATUS_DUPLICATE_IGNORED,
        ]);
        WebhookLog::create([
            'provider' => 'midtrans',
            'order_id' => 'PAY-UNKNOWN',
            'payload' => ['order_id' => 'PAY-UNKNOWN'],
            'signature_valid' => false,
            'status' => WebhookLog::STATUS_REJECTED,
        ]);
        WebhookLog::create([
            'provider' => 'midtrans',
            'order_id' => 'PAY-UNKNOWN-2',
            'payload' => ['order_id' => 'PAY-UNKNOWN-2'],
            'signature_valid' => true,
            'status' => WebhookLog::STATUS_ERROR,
        ]);

        $summary = $this->midtransService->getFinancialSummary();

        $this->assertEquals(50000.0, $summary['total_dp_revenue']);
        $this->assertEquals(170000.0, $summary['total_revenue']);
        $this->assertEquals(2, $summary['settlement_count']);
        $this->assertEquals(1, $summary['pending_count']);
        $this->assertEquals(1, $summary['expired_count']);
        $this->assertEquals(1, $summary['cancel_count']);
        $this->assertEquals(1, $summary['refund_count']);
        $this->assertEquals(4, $summary['webhook_total']);
        $this->assertEquals(1, $summary['webhook_rejected']);
        $this->assertEquals(1, $summary['webhook_duplicate_ignored']);
        $this->assertEquals(1, $summary['webhook_error']);
    }

    /**
     * Test getFinancialSummary filtered by specific court IDs.
     */
    public function test_financial_summary_with_court_filter(): void
    {
        // Court 1 Booking
        $booking1 = Booking::create([
            'code' => 'BK-C1-10',
            'user_id' => $this->user->id,
            'court_id' => $this->court1->id,
            'total_price' => 100000,
            'dp_amount' => 50000,
            'status' => Booking::STATUS_CONFIRMED,
            'expires_at' => Carbon::now()->addHour(),
        ]);
        Payment::create([
            'booking_id' => $booking1->id,
            'order_id' => 'PAY-C1-10-DP',
            'type' => Payment::TYPE_DP,
            'amount' => 50000,
            'status' => Payment::STATUS_SETTLEMENT,
        ]);

        // Court 2 Booking
        $booking2 = Booking::create([
            'code' => 'BK-C2-20',
            'user_id' => $this->user->id,
            'court_id' => $this->court2->id,
            'total_price' => 200000,
            'dp_amount' => 80000,
            'status' => Booking::STATUS_CONFIRMED,
            'expires_at' => Carbon::now()->addHour(),
        ]);
        Payment::create([
            'booking_id' => $booking2->id,
            'order_id' => 'PAY-C2-20-DP',
            'type' => Payment::TYPE_DP,
            'amount' => 80000,
            'status' => Payment::STATUS_SETTLEMENT,
        ]);

        // Webhook logs for each
        WebhookLog::create([
            'provider' => 'midtrans',
            'order_id' => 'PAY-C1-10-DP',
            'payload' => ['order_id' => 'PAY-C1-10-DP'],
            'signature_valid' => true,
            'status' => WebhookLog::STATUS_PROCESSED,
        ]);
        WebhookLog::create([
            'provider' => 'midtrans',
            'order_id' => 'PAY-C2-20-DP',
            'payload' => ['order_id' => 'PAY-C2-20-DP'],
            'signature_valid' => true,
            'status' => WebhookLog::STATUS_PROCESSED,
        ]);
        WebhookLog::create([
            'provider' => 'midtrans',
            'order_id' => 'PAY-C1-10-DP',
            'payload' => ['order_id' => 'PAY-C1-10-DP'],
            'signature_valid' => false,
            'status' => WebhookLog::STATUS_REJECTED,
        ]);

        // Summary only for Court 1
        $summary = $this->midtransService->getFinancialSummary([$this->court1->id]);

        $this->assertEquals(50000.0, $summary['total_dp_revenue']);
        $this->assertEquals(50000.0, $summary['total_revenue']);
        $this->assertEquals(1, $summary['settlement_count']);
        $this->assertEquals(0, $summary['pending_count']);
        $this->assertEquals(2, $summary['webhook_total']);
        $this->assertEquals(1, $summary['webhook_rejected']);
        $this->assertEquals(0, $summary['webhook_duplicate_ignored']);
        $this->assertEquals(0, $summary['webhook_error']);
    }

    /**
     * Test WebhookLog scopeFailed filters correctly.
     */
    public function test_webhook_log_failed_scope(): void
    {
        WebhookLog::create([
            'provider' => 'midtrans',
            'order_id' => 'LOG-1',
            'payload' => [],
            'signature_valid' => true,
            'status' => WebhookLog::STATUS_PROCESSED,
        ]);
        WebhookLog::create([
            'provider' => 'midtrans',
            'order_id' => 'LOG-2',
            'payload' => [],
            'signature_valid' => false,
            'status' => WebhookLog::STATUS_REJECTED,
        ]);
        WebhookLog::create([
            'provider' => 'midtrans',
            'order_id' => 'LOG-3',
            'payload' => [],
            'signature_valid' => true,
            'status' => WebhookLog::STATUS_ERROR,
        ]);
        WebhookLog::create([
            'provider' => 'midtrans',
            'order_id' => 'LOG-4',
            'payload' => [],
            'signature_valid' => true,
            'status' => WebhookLog::STATUS_DUPLICATE_IGNORED,
        ]);

        $failedLogs = WebhookLog::failed()->get();
        $this->assertCount(2, $failedLogs);
        $this->assertTrue($failedLogs->contains('order_id', 'LOG-2'));
        $this->assertTrue($failedLogs->contains('order_id', 'LOG-3'));
    }
}
