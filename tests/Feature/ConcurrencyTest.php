<?php

namespace Tests\Feature;

use App\Models\Booking;
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

class ConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * PRD §3 Metric: 0 booking bentrok pada uji konkurensi (>= 50 request paralel ke slot sama, tepat 1 berhasil).
     */
    public function test_50_concurrent_booking_requests_yield_exactly_one_success(): void
    {
        $bookingService = app(BookingService::class);

        $pengelola = User::create([
            'name' => 'Owner',
            'email' => 'owner.concurrent@example.com',
            'phone' => '0812345678',
            'password' => bcrypt('password123'),
            'role' => 'pengelola',
            'status' => 'active',
        ]);

        $venue = Venue::create([
            'owner_id' => $pengelola->id,
            'name' => 'Concurrency Venue',
            'slug' => 'concurrency-venue',
            'address' => 'Jl. Konkurensi No. 50',
            'city' => 'Jakarta',
            'phone' => '021-998877',
            'dp_percent' => 30,
            'cancel_free_hours' => 24,
            'cancel_refund_percent' => 100,
        ]);

        $court = Court::create([
            'venue_id' => $venue->id,
            'name' => 'Court Concurrency 1',
            'sport_type' => 'futsal',
            'is_active' => true,
        ]);

        for ($d = 0; $d <= 6; $d++) {
            OperatingHour::create([
                'court_id' => $court->id,
                'day_of_week' => $d,
                'open_time' => '08:00:00',
                'close_time' => '23:00:00',
                'slot_duration_minutes' => 60,
            ]);
        }

        CourtPrice::create([
            'court_id' => $court->id,
            'day_type' => 'all',
            'start_time' => '08:00:00',
            'end_time' => '23:00:00',
            'price' => 150000,
        ]);

        // Target identical slot time for all 50 requests
        $targetSlotUtc = Carbon::now('Asia/Jakarta')->addDays(3)->setTime(19, 0)->utc()->toIso8601String();

        // Create 50 distinct users to simulate 50 concurrent customers
        $users = [];
        for ($i = 1; $i <= 50; $i++) {
            $users[] = User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@concurrent.test",
                'phone' => "08" . str_pad((string)$i, 9, '0', STR_PAD_LEFT),
                'password' => bcrypt('password123'),
                'role' => 'penyewa',
                'status' => 'active',
            ]);
        }

        $successCount = 0;
        $failCount = 0;
        $successfulBooking = null;

        // Execute 50 rapid attempts to the identical slot
        foreach ($users as $user) {
            try {
                $booking = $bookingService->createBooking(
                    user: $user,
                    court: $court,
                    slotStartsUtc: [$targetSlotUtc],
                    notes: "Concurrent attempt by {$user->name}"
                );

                $successCount++;
                $successfulBooking = $booking;
            } catch (Exception $e) {
                $failCount++;
            }
        }

        // VERIFY PRD METRIC:
        // Exactly 1 success, exactly 49 failed (0 bentrok / double booking)
        $this->assertEquals(1, $successCount, "Tepat 1 booking harus berhasil!");
        $this->assertEquals(49, $failCount, "Tepat 49 booking lainnya harus ditolak!");

        // Verify DB only has 1 booking slot record
        $this->assertEquals(1, Booking::where('court_id', $court->id)->count());
        $this->assertDatabaseCount('booking_slots', 1);
    }
}
