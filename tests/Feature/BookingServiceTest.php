<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\CourtPrice;
use App\Models\OperatingHour;
use App\Models\User;
use App\Models\Venue;
use App\Services\BookingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $penyewa;
    protected User $penyewa2;
    protected User $pengelola;
    protected Venue $venue;
    protected Court $court;
    protected BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookingService = app(BookingService::class);

        $this->pengelola = User::create([
            'name' => 'Owner Budi',
            'email' => 'budi.owner@example.com',
            'phone' => '0812345678',
            'password' => bcrypt('password123'),
            'role' => 'pengelola',
            'status' => 'active',
        ]);

        $this->penyewa = User::create([
            'name' => 'Penyewa Andi',
            'email' => 'andi@example.com',
            'phone' => '0898765432',
            'password' => bcrypt('password123'),
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $this->penyewa2 = User::create([
            'name' => 'Penyewa Citra',
            'email' => 'citra@example.com',
            'phone' => '0811223344',
            'password' => bcrypt('password123'),
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $this->venue = Venue::create([
            'owner_id' => $this->pengelola->id,
            'name' => 'Arena Sport Futsal',
            'slug' => 'arena-sport-futsal',
            'address' => 'Jl. Sudirman No. 1',
            'city' => 'Jakarta',
            'phone' => '021-1234567',
            'dp_percent' => 30,
            'cancel_free_hours' => 24,
            'cancel_refund_percent' => 100,
        ]);

        $this->court = Court::create([
            'venue_id' => $this->venue->id,
            'name' => 'Court 1 Vinyl',
            'sport_type' => 'futsal',
            'is_active' => true,
        ]);

        // Operating hours: all days 08:00 - 23:00
        for ($day = 0; $day <= 6; $day++) {
            OperatingHour::create([
                'court_id' => $this->court->id,
                'day_of_week' => $day,
                'open_time' => '08:00:00',
                'close_time' => '23:00:00',
                'slot_duration_minutes' => 60,
            ]);
        }

        // Price: 100,000 / hour
        CourtPrice::create([
            'court_id' => $this->court->id,
            'day_type' => 'all',
            'start_time' => '08:00:00',
            'end_time' => '23:00:00',
            'price' => 100000,
        ]);
    }

    /**
     * Test real-time availability returns slot list.
     */
    public function test_get_availability_returns_slots(): void
    {
        $targetDate = Carbon::now('Asia/Jakarta')->addDays(3)->toDateString();
        $availability = $this->bookingService->getAvailability($this->court, $targetDate);

        $this->assertNotEmpty($availability['slots']);
        $this->assertEquals($targetDate, $availability['date']);
        $this->assertEquals('Court 1 Vinyl', $availability['court']['name']);
        $this->assertEquals('available', $availability['slots'][0]['status']);
    }

    /**
     * Test creating a booking successfully holds slot for 15 minutes.
     */
    public function test_create_booking_holds_slot_and_calculates_dp(): void
    {
        $slotUtc = Carbon::now('Asia/Jakarta')->addDays(3)->setTime(14, 0)->utc()->toIso8601String();

        $booking = $this->bookingService->createBooking(
            user: $this->penyewa,
            court: $this->court,
            slotStartsUtc: [$slotUtc],
            notes: 'Test booking'
        );

        $this->assertNotNull($booking);
        $this->assertEquals(Booking::STATUS_PENDING_PAYMENT, $booking->status);
        $this->assertEquals(100000.00, $booking->total_price);
        $this->assertEquals(30000.00, $booking->dp_amount); // 30% of 100,000
        $this->assertTrue(Carbon::now()->addMinutes(14)->lte($booking->expires_at));

        $this->assertDatabaseHas('booking_slots', [
            'booking_id' => $booking->id,
            'court_id' => $this->court->id,
        ]);
    }

    /**
     * Critical Anti-Double-Booking Test (§9.2):
     * Ensures another booking attempt on the exact same slot is strictly rejected.
     */
    public function test_atomic_prevention_of_double_booking_rejects_second_request(): void
    {
        $slotUtc = Carbon::now('Asia/Jakarta')->addDays(4)->setTime(16, 0)->utc()->toIso8601String();

        // 1. First booking succeeds
        $firstBooking = $this->bookingService->createBooking(
            user: $this->penyewa,
            court: $this->court,
            slotStartsUtc: [$slotUtc]
        );
        $this->assertEquals(Booking::STATUS_PENDING_PAYMENT, $firstBooking->status);

        // 2. Second booking to the exact same slot MUST throw Exception
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/sudah terisi atau di-hold|sedang dalam proses/');

        $this->bookingService->createBooking(
            user: $this->penyewa2,
            court: $this->court,
            slotStartsUtc: [$slotUtc]
        );
    }

    /**
     * Test auto-expire of pending booking releases the slot.
     */
    public function test_expired_booking_releases_slot(): void
    {
        $slotUtc = Carbon::now('Asia/Jakarta')->addDays(5)->setTime(10, 0)->utc()->toIso8601String();

        $booking = $this->bookingService->createBooking(
            user: $this->penyewa,
            court: $this->court,
            slotStartsUtc: [$slotUtc]
        );

        // Simulate expiration: travel forward 16 minutes
        $this->travel(16)->minutes();

        $count = $this->bookingService->expireStalePendingBookings();
        $this->assertGreaterThanOrEqual(1, $count);

        $booking->refresh();
        $this->assertEquals(Booking::STATUS_EXPIRED, $booking->status);

        // The slot should be removed from booking_slots so it can be booked again
        $this->assertDatabaseMissing('booking_slots', [
            'booking_id' => $booking->id,
        ]);

        // A new user can now book the same slot without error
        $newBooking = $this->bookingService->createBooking(
            user: $this->penyewa2,
            court: $this->court,
            slotStartsUtc: [$slotUtc]
        );
        $this->assertEquals(Booking::STATUS_PENDING_PAYMENT, $newBooking->status);
    }

    /**
     * Test cancellation calculates refund according to venue snapshot (FR-15).
     */
    public function test_cancellation_calculates_refund_correctly(): void
    {
        // Book a slot 48 hours in future (> 24 hours free cancellation policy)
        $slotUtc = Carbon::now('Asia/Jakarta')->addHours(48)->setTime(18, 0)->utc()->toIso8601String();

        $booking = $this->bookingService->createBooking(
            user: $this->penyewa,
            court: $this->court,
            slotStartsUtc: [$slotUtc]
        );

        // Mark as confirmed
        $booking->update(['status' => Booking::STATUS_CONFIRMED]);

        $cancelled = $this->bookingService->cancelBooking($booking, $this->penyewa, 'Emergency change');

        $this->assertEquals(Booking::STATUS_CANCELLED, $cancelled->status);
        // 100% of 30,000 DP should be refunded because cancelled 48h > 24h
        $this->assertEquals(30000.00, $cancelled->refund_amount);

        // Slot must be deleted from booking_slots
        $this->assertDatabaseMissing('booking_slots', [
            'booking_id' => $booking->id,
        ]);
    }
}
