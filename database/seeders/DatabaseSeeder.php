<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\CourtPrice;
use App\Models\OperatingHour;
use App\Models\Payment;
use App\Models\ScheduleBlock;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@harkat.ac.id'],
            [
                'name' => 'Administrator Platform',
                'phone' => '081234567890',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $pengelolaActive = User::firstOrCreate(
            ['email' => 'budi.pengelola@gmail.com'],
            [
                'name' => 'Budi Santoso (Pengelola Venue)',
                'phone' => '081398765432',
                'password' => Hash::make('password123'),
                'role' => 'pengelola',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $pengelolaPending = User::firstOrCreate(
            ['email' => 'hendra.pending@gmail.com'],
            [
                'name' => 'Hendra Wijaya (Calon Pengelola)',
                'phone' => '081512344321',
                'password' => Hash::make('password123'),
                'role' => 'pengelola',
                'status' => 'pending', // Pending approval by Admin
                'email_verified_at' => now(),
            ]
        );

        $penyewa = User::firstOrCreate(
            ['email' => 'arif.penyewa@gmail.com'],
            [
                'name' => 'Moh. Arif Prasetyo (Penyewa)',
                'phone' => '081987654321',
                'password' => Hash::make('password123'),
                'role' => 'penyewa',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 2. Venues
        $venue1 = Venue::firstOrCreate(
            ['slug' => 'champions-sport-arena-jakarta'],
            [
                'owner_id' => $pengelolaActive->id,
                'name' => 'Champions Sport Arena Jakarta',
                'description' => 'Arena olahraga terpadu dengan standar internasional. Dilengkapi rumput sintetis futsal berstandar FIFA, karpet badminton Yonex, dan lapangan basket kayu jati.',
                'address' => 'Jl. TB Simatupang No. 88, Cilandak',
                'city' => 'Jakarta Selatan',
                'phone' => '021-7890123',
                'dp_percent' => 30, // 30% DP
                'cancel_free_hours' => 24, // 24 hours free cancellation
                'cancel_refund_percent' => 100, // 100% DP refunded if cancelled > 24 hours
                'image_url' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1200&q=80',
            ]
        );

        $venue2 = Venue::firstOrCreate(
            ['slug' => 'starlight-mini-soccer-badminton'],
            [
                'owner_id' => $pengelolaActive->id,
                'name' => 'Starlight Mini Soccer & Badminton Arena',
                'description' => 'Pusat kebugaran dan pertandingan mini soccer & badminton favorit dengan fasilitas locker room, shower air hangat, kantin, dan tribun penonton.',
                'address' => 'Jl. Boulevard Gading Serpong Kav. 12',
                'city' => 'Tangerang',
                'phone' => '021-5467890',
                'dp_percent' => 30,
                'cancel_free_hours' => 12,
                'cancel_refund_percent' => 80,
                'image_url' => 'https://images.unsplash.com/photo-1529900245534-47fbfb57835a?auto=format&fit=crop&w=1200&q=80',
            ]
        );

        // 3. Courts
        $courtsData = [
            [
                'venue_id' => $venue1->id,
                'name' => 'Lapangan Futsal A (Vinyl Pro)',
                'sport_type' => 'futsal',
                'prices' => [
                    ['day_type' => 'weekday', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'price' => 120000],
                    ['day_type' => 'weekday', 'start_time' => '17:00:00', 'end_time' => '23:00:00', 'price' => 180000], // Prime
                    ['day_type' => 'weekend', 'start_time' => '08:00:00', 'end_time' => '23:00:00', 'price' => 200000],
                ],
            ],
            [
                'venue_id' => $venue1->id,
                'name' => 'Lapangan Badminton 1 (Karpet Yonex)',
                'sport_type' => 'badminton',
                'prices' => [
                    ['day_type' => 'weekday', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'price' => 60000],
                    ['day_type' => 'weekday', 'start_time' => '17:00:00', 'end_time' => '23:00:00', 'price' => 85000],
                    ['day_type' => 'weekend', 'start_time' => '08:00:00', 'end_time' => '23:00:00', 'price' => 95000],
                ],
            ],
            [
                'venue_id' => $venue1->id,
                'name' => 'Lapangan Basket Indoor',
                'sport_type' => 'basketball',
                'prices' => [
                    ['day_type' => 'weekday', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'price' => 150000],
                    ['day_type' => 'weekday', 'start_time' => '17:00:00', 'end_time' => '23:00:00', 'price' => 220000],
                    ['day_type' => 'weekend', 'start_time' => '08:00:00', 'end_time' => '23:00:00', 'price' => 250000],
                ],
            ],
            [
                'venue_id' => $venue2->id,
                'name' => 'Mini Soccer Field 7 vs 7 (Rumput Sintetis)',
                'sport_type' => 'mini_soccer',
                'prices' => [
                    ['day_type' => 'weekday', 'start_time' => '08:00:00', 'end_time' => '16:00:00', 'price' => 350000],
                    ['day_type' => 'weekday', 'start_time' => '16:00:00', 'end_time' => '23:00:00', 'price' => 500000],
                    ['day_type' => 'weekend', 'start_time' => '08:00:00', 'end_time' => '23:00:00', 'price' => 600000],
                ],
            ],
            [
                'venue_id' => $venue2->id,
                'name' => 'Lapangan Badminton A',
                'sport_type' => 'badminton',
                'prices' => [
                    ['day_type' => 'all', 'start_time' => '08:00:00', 'end_time' => '23:00:00', 'price' => 70000],
                ],
            ],
        ];

        foreach ($courtsData as $c) {
            $court = Court::firstOrCreate(
                ['venue_id' => $c['venue_id'], 'name' => $c['name']],
                ['sport_type' => $c['sport_type'], 'is_active' => true]
            );

            // Operating hours: Sun-Sat 08:00 - 23:00
            for ($d = 0; $d <= 6; $d++) {
                OperatingHour::firstOrCreate(
                    ['court_id' => $court->id, 'day_of_week' => $d],
                    ['open_time' => '08:00:00', 'close_time' => '23:00:00', 'slot_duration_minutes' => 60]
                );
            }

            // Prices
            foreach ($c['prices'] as $p) {
                CourtPrice::firstOrCreate(
                    [
                        'court_id' => $court->id,
                        'day_type' => $p['day_type'],
                        'start_time' => $p['start_time'],
                        'end_time' => $p['end_time'],
                    ],
                    ['price' => $p['price']]
                );
            }
        }

        // 4. Sample Schedule Block (Maintenance tomorrow at 12:00 - 14:00 on Court 1)
        $firstCourt = Court::first();
        if ($firstCourt) {
            $tomorrow = Carbon::now('Asia/Jakarta')->addDay()->toDateString();
            ScheduleBlock::firstOrCreate(
                [
                    'court_id' => $firstCourt->id,
                    'start_at' => Carbon::parse("{$tomorrow} 12:00:00", 'Asia/Jakarta')->utc(),
                    'end_at' => Carbon::parse("{$tomorrow} 14:00:00", 'Asia/Jakarta')->utc(),
                ],
                ['reason' => 'Perawatan rutin jaring gawang & pembersihan lantai vinyl']
            );
        }

        // 5. Seed a Sample Confirmed Booking
        if ($firstCourt) {
            $bookDate = Carbon::now('Asia/Jakarta')->addDays(2)->toDateString();
            $slotStartUtc = Carbon::parse("{$bookDate} 19:00:00", 'Asia/Jakarta')->utc();
            $slotEndUtc = Carbon::parse("{$bookDate} 20:00:00", 'Asia/Jakarta')->utc();

            $bookingCode = 'BK-' . date('Ymd') . '-DEMO1';
            $booking = Booking::firstOrCreate(
                ['code' => $bookingCode],
                [
                    'user_id' => $penyewa->id,
                    'court_id' => $firstCourt->id,
                    'total_price' => 180000,
                    'dp_amount' => 54000, // 30%
                    'status' => Booking::STATUS_CONFIRMED,
                    'expires_at' => Carbon::now()->addMinutes(15),
                    'cancel_free_hours_snapshot' => 24,
                    'cancel_refund_percent_snapshot' => 100,
                    'notes' => 'Latihan tim futsal kampus Harkat Negeri',
                ]
            );

            BookingSlot::firstOrCreate(
                ['court_id' => $firstCourt->id, 'slot_start' => $slotStartUtc],
                [
                    'booking_id' => $booking->id,
                    'slot_end' => $slotEndUtc,
                    'price' => 180000,
                ]
            );

            Payment::firstOrCreate(
                ['booking_id' => $booking->id, 'order_id' => 'PAY-' . $booking->code . '-DP-SEED'],
                [
                    'type' => 'DP',
                    'amount' => 54000,
                    'status' => Payment::STATUS_SETTLEMENT,
                    'payment_type' => 'qris',
                    'gateway_ref' => 'trx-midtrans-demo-settled-1',
                    'paid_at' => Carbon::now(),
                ]
            );
        }
    }
}
