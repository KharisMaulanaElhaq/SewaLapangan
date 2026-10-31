<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentInitTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Venue $venue;
    protected Court $court;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Kharis Penyewa',
            'email' => 'kharis@example.com',
            'phone' => '08123456789',
            'password' => bcrypt('password123'),
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $this->otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'phone' => '08987654321',
            'password' => bcrypt('password123'),
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $owner = User::create([
            'name' => 'Venue Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password123'),
            'role' => 'pengelola',
            'status' => 'active',
        ]);

        $this->venue = Venue::create([
            'owner_id' => $owner->id,
            'name' => 'Gelora Futsal Arena',
            'slug' => 'gelora-futsal-arena',
            'address' => 'Jl. Pemuda No. 10',
            'city' => 'Jakarta Pusat',
            'phone' => '0812345678',
            'dp_percent' => 30,
            'cancel_free_hours' => 24,
            'cancel_refund_percent' => 100,
        ]);

        $this->court = Court::create([
            'venue_id' => $this->venue->id,
            'name' => 'Lapangan Vinyl 1',
            'sport_type' => 'futsal',
            'is_active' => true,
        ]);

        $this->booking = Booking::create([
            'code' => 'BK-TEST-INIT-01',
            'user_id' => $this->user->id,
            'court_id' => $this->court->id,
            'total_price' => 200000,
            'dp_amount' => 60000,
            'status' => Booking::STATUS_PENDING_PAYMENT,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);
    }

    /**
     * Test successful payment initialization returns HTTP 201 Created and contract structure.
     */
    public function test_successful_payment_initialization_returns_201_and_contract_structure(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/bookings/{$this->booking->id}/pay");

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'status',
            'snap_token',
            'redirect_url',
            'order_id',
            'amount',
            'message',
            'data' => [
                'id',
                'booking_id',
                'order_id',
                'type',
                'amount',
                'status',
                'payment_type',
                'gateway_ref',
                'snap_token',
                'snap_redirect_url',
                'redirect_url',
                'paid_at',
                'paid_at_local',
                'created_at',
            ],
        ]);

        $response->assertJson([
            'status' => 'success',
            'amount' => 60000.0,
            'data' => [
                'booking_id' => $this->booking->id,
                'status' => Payment::STATUS_PENDING,
            ],
        ]);

        $data = $response->json();
        $this->assertNotEmpty($data['snap_token']);
        $this->assertNotEmpty($data['redirect_url']);
        $this->assertEquals($data['redirect_url'], $data['data']['redirect_url']);
        $this->assertEquals($data['snap_token'], $data['data']['snap_token']);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'status' => Payment::STATUS_PENDING,
            'type' => 'DP',
        ]);
    }

    /**
     * Test booking with CONFIRMED status returns HTTP 409 Conflict with PAYMENT_NOT_ALLOWED code.
     */
    public function test_confirmed_booking_returns_409_with_payment_not_allowed_code(): void
    {
        Sanctum::actingAs($this->user);

        $this->booking->update([
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->postJson("/api/v1/bookings/{$this->booking->id}/pay");

        $response->assertStatus(409);
        $response->assertExactJson([
            'status' => 'error',
            'message' => 'Transaksi sudah dibayar atau telah melewati batas holding pembayaran.',
            'code' => 'PAYMENT_NOT_ALLOWED',
        ]);
    }

    /**
     * Test expired booking returns HTTP 409 Conflict with PAYMENT_NOT_ALLOWED code.
     */
    public function test_expired_booking_returns_409_with_payment_not_allowed_code(): void
    {
        Sanctum::actingAs($this->user);

        $this->booking->update([
            'expires_at' => Carbon::now()->subMinutes(5),
        ]);

        $response = $this->postJson("/api/v1/bookings/{$this->booking->id}/pay");

        $response->assertStatus(409);
        $response->assertExactJson([
            'status' => 'error',
            'message' => 'Transaksi sudah dibayar atau telah melewati batas holding pembayaran.',
            'code' => 'PAYMENT_NOT_ALLOWED',
        ]);
    }

    /**
     * Test user who does not own the booking is forbidden (HTTP 403).
     */
    public function test_non_owner_cannot_initiate_payment(): void
    {
        Sanctum::actingAs($this->otherUser);

        $response = $this->postJson("/api/v1/bookings/{$this->booking->id}/pay");

        $response->assertStatus(403);
    }
}
