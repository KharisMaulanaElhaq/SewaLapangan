<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengelolaDashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengelola_dashboard_displays_correct_summary_and_isolates_data(): void
    {
        // 1. Create Customer
        $customer = User::create([
            'name' => 'Customer Test',
            'email' => 'customer.test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        // 2. Create Pengelola A & Venue A & Court A
        $pengelolaA = User::create([
            'name' => 'Pengelola Venue A',
            'email' => 'pengelola.a@example.com',
            'password' => bcrypt('password123'),
            'role' => 'pengelola',
            'status' => 'active',
        ]);

        $venueA = Venue::create([
            'owner_id' => $pengelolaA->id,
            'name' => 'Arena Sport A',
            'slug' => 'arena-sport-a',
            'address' => 'Jl. Pengelola A No. 1',
            'city' => 'Jakarta',
            'phone' => '081111111111',
            'dp_percent' => 30,
        ]);

        $courtA = Court::create([
            'venue_id' => $venueA->id,
            'name' => 'Lapangan Futsal A',
            'sport_type' => 'futsal',
            'is_active' => true,
        ]);

        // Pengelola A: DP settlement (50,000)
        $bookingA1 = Booking::create([
            'code' => 'BK-A-01',
            'user_id' => $customer->id,
            'court_id' => $courtA->id,
            'total_price' => 150000,
            'dp_amount' => 50000,
            'status' => Booking::STATUS_CONFIRMED,
            'expires_at' => Carbon::now()->addHour(),
        ]);
        Payment::create([
            'booking_id' => $bookingA1->id,
            'order_id' => 'PAY-A-01-DP-SETTLED',
            'type' => Payment::TYPE_DP,
            'amount' => 50000,
            'status' => Payment::STATUS_SETTLEMENT,
            'paid_at' => Carbon::now(),
        ]);

        // Pengelola A: DP pending (40,000)
        $bookingA2 = Booking::create([
            'code' => 'BK-A-02',
            'user_id' => $customer->id,
            'court_id' => $courtA->id,
            'total_price' => 120000,
            'dp_amount' => 40000,
            'status' => Booking::STATUS_PENDING_PAYMENT,
            'expires_at' => Carbon::now()->addHour(),
        ]);
        Payment::create([
            'booking_id' => $bookingA2->id,
            'order_id' => 'PAY-A-02-DP-PENDING',
            'type' => Payment::TYPE_DP,
            'amount' => 40000,
            'status' => Payment::STATUS_PENDING,
        ]);

        // Pengelola A: Lunas settlement (100,000)
        $bookingA3 = Booking::create([
            'code' => 'BK-A-03',
            'user_id' => $customer->id,
            'court_id' => $courtA->id,
            'total_price' => 100000,
            'dp_amount' => 30000,
            'status' => Booking::STATUS_CONFIRMED,
            'expires_at' => Carbon::now()->addHour(),
        ]);
        Payment::create([
            'booking_id' => $bookingA3->id,
            'order_id' => 'PAY-A-03-LUNAS-SETTLED',
            'type' => Payment::TYPE_LUNAS,
            'amount' => 100000,
            'status' => Payment::STATUS_SETTLEMENT,
            'paid_at' => Carbon::now(),
        ]);

        // 3. Create Pengelola B & Venue B & Court B
        $pengelolaB = User::create([
            'name' => 'Pengelola Venue B',
            'email' => 'pengelola.b@example.com',
            'password' => bcrypt('password123'),
            'role' => 'pengelola',
            'status' => 'active',
        ]);

        $venueB = Venue::create([
            'owner_id' => $pengelolaB->id,
            'name' => 'Arena Sport B',
            'slug' => 'arena-sport-b',
            'address' => 'Jl. Pengelola B No. 2',
            'city' => 'Bandung',
            'phone' => '082222222222',
            'dp_percent' => 30,
        ]);

        $courtB = Court::create([
            'venue_id' => $venueB->id,
            'name' => 'Lapangan Badminton B',
            'sport_type' => 'badminton',
            'is_active' => true,
        ]);

        // Pengelola B: DP settlement (75,000)
        $bookingB1 = Booking::create([
            'code' => 'BK-B-01',
            'user_id' => $customer->id,
            'court_id' => $courtB->id,
            'total_price' => 250000,
            'dp_amount' => 75000,
            'status' => Booking::STATUS_CONFIRMED,
            'expires_at' => Carbon::now()->addHour(),
        ]);
        Payment::create([
            'booking_id' => $bookingB1->id,
            'order_id' => 'PAY-B-01-DP-SETTLED',
            'type' => Payment::TYPE_DP,
            'amount' => 75000,
            'status' => Payment::STATUS_SETTLEMENT,
            'paid_at' => Carbon::now(),
        ]);

        // Pengelola B: DP pending (65,000)
        $bookingB2 = Booking::create([
            'code' => 'BK-B-02',
            'user_id' => $customer->id,
            'court_id' => $courtB->id,
            'total_price' => 200000,
            'dp_amount' => 65000,
            'status' => Booking::STATUS_PENDING_PAYMENT,
            'expires_at' => Carbon::now()->addHour(),
        ]);
        Payment::create([
            'booking_id' => $bookingB2->id,
            'order_id' => 'PAY-B-02-DP-PENDING',
            'type' => Payment::TYPE_DP,
            'amount' => 65000,
            'status' => Payment::STATUS_PENDING,
        ]);

        // Pengelola B: Lunas settlement (200,000)
        $bookingB3 = Booking::create([
            'code' => 'BK-B-03',
            'user_id' => $customer->id,
            'court_id' => $courtB->id,
            'total_price' => 200000,
            'dp_amount' => 60000,
            'status' => Booking::STATUS_CONFIRMED,
            'expires_at' => Carbon::now()->addHour(),
        ]);
        Payment::create([
            'booking_id' => $bookingB3->id,
            'order_id' => 'PAY-B-03-LUNAS-SETTLED',
            'type' => Payment::TYPE_LUNAS,
            'amount' => 200000,
            'status' => Payment::STATUS_SETTLEMENT,
            'paid_at' => Carbon::now(),
        ]);

        // 4. Login as Pengelola A
        $response = $this->actingAs($pengelolaA)->get('/dashboard/pengelola');

        $response->assertStatus(200);

        // Assert view variables
        $response->assertViewHas('summary', function (array $summary) {
            return $summary['total_dp_revenue'] == 50000
                && $summary['settlement_count'] === 2
                && $summary['pending_count'] === 1;
        });

        // totalRevenue is set to DP revenue for backward compatibility
        $response->assertViewHas('totalRevenue', 50000);

        // Assert HTML content for Pengelola A
        $response->assertSee('Pendapatan DP (Settled)');
        $response->assertSee('Rp 50.000');
        $response->assertSee('Transaksi Settlement');
        $response->assertSee('Transaksi Pending');

        // Assert Pengelola B data is NOT leaked to Pengelola A dashboard
        $response->assertDontSee('Rp 75.000');
        $response->assertDontSee('75.000');
        $response->assertDontSee('Arena Sport B');
        $response->assertDontSee('Lapangan Badminton B');
    }

    public function test_pengelola_dashboard_handles_empty_data_correctly(): void
    {
        $newPengelola = User::create([
            'name' => 'Pengelola Baru',
            'email' => 'pengelola.baru@example.com',
            'password' => bcrypt('password123'),
            'role' => 'pengelola',
            'status' => 'active',
        ]);

        $response = $this->actingAs($newPengelola)->get('/dashboard/pengelola');

        $response->assertStatus(200);

        $response->assertViewHas('summary', function (array $summary) {
            return $summary['total_dp_revenue'] == 0
                && $summary['settlement_count'] === 0
                && $summary['pending_count'] === 0;
        });

        $response->assertViewHas('totalRevenue', 0);
        $response->assertSee('Rp 0');
    }
}
