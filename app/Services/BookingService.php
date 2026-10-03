<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\ScheduleBlock;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Get slot availability for a court on a given date (Asia/Jakarta / Y-m-d).
     */
    public function getAvailability(Court $court, string $dateString): array
    {
        $court->loadMissing(['venue', 'operatingHours', 'courtPrices', 'scheduleBlocks']);
        $venue = $court->venue;

        $targetDate = Carbon::parse($dateString, 'Asia/Jakarta')->startOfDay();
        $dayOfWeek = $targetDate->dayOfWeek; // 0=Sunday, 1=Monday... 6=Saturday
        $isWeekend = $targetDate->isWeekend();

        // 1. Operating hours for this day of week
        $operatingHour = $court->operatingHours->firstWhere('day_of_week', $dayOfWeek);
        if (!$operatingHour) {
            // Default operating hour if none configured: 08:00 - 23:00, 60 mins
            $openTimeStr = '08:00:00';
            $closeTimeStr = '23:00:00';
            $slotDuration = 60;
        } else {
            $openTimeStr = $operatingHour->open_time;
            $closeTimeStr = $operatingHour->close_time;
            $slotDuration = $operatingHour->slot_duration_minutes ?: 60;
        }

        $openTime = Carbon::parse("{$dateString} {$openTimeStr}", 'Asia/Jakarta');
        $closeTime = Carbon::parse("{$dateString} {$closeTimeStr}", 'Asia/Jakarta');

        // Fetch active schedule blocks for this court on this day
        $dayStartUtc = $targetDate->copy()->startOfDay()->utc();
        $dayEndUtc = $targetDate->copy()->endOfDay()->utc();

        $scheduleBlocks = ScheduleBlock::where('court_id', $court->id)
            ->where(function ($q) use ($dayStartUtc, $dayEndUtc) {
                $q->whereBetween('start_at', [$dayStartUtc, $dayEndUtc])
                  ->orWhereBetween('end_at', [$dayStartUtc, $dayEndUtc])
                  ->orWhere(function ($sub) use ($dayStartUtc, $dayEndUtc) {
                      $sub->where('start_at', '<=', $dayStartUtc)
                          ->where('end_at', '>=', $dayEndUtc);
                  });
            })
            ->get();

        // Auto expire stale pending bookings first to ensure fresh slot states
        $this->expireStalePendingBookings();

        // Fetch existing booked or held slots for this court
        $existingSlots = BookingSlot::with('booking')
            ->where('court_id', $court->id)
            ->whereBetween('slot_start', [$dayStartUtc, $dayEndUtc])
            ->get();

        $slots = [];
        $currentCursor = $openTime->copy();

        while ($currentCursor->copy()->addMinutes($slotDuration)->lte($closeTime)) {
            $slotStartLocal = $currentCursor->copy();
            $slotEndLocal = $currentCursor->copy()->addMinutes($slotDuration);

            $slotStartUtc = $slotStartLocal->copy()->utc();
            $slotEndUtc = $slotEndLocal->copy()->utc();

            // Calculate price based on court prices
            $price = $this->calculateSlotPrice($court, $slotStartLocal, $isWeekend);

            // Check if slot falls in schedule block
            $isBlocked = $scheduleBlocks->contains(function ($block) use ($slotStartUtc, $slotEndUtc) {
                return ($slotStartUtc < $block->end_at) && ($slotEndUtc > $block->start_at);
            });

            // Check if slot is occupied in booking_slots
            $matchedSlot = $existingSlots->first(function ($s) use ($slotStartUtc) {
                return Carbon::parse($s->slot_start)->equalTo($slotStartUtc);
            });

            $status = 'available';
            $bookingInfo = null;

            if ($isBlocked) {
                $status = 'blocked';
            } elseif ($matchedSlot && $matchedSlot->booking) {
                $booking = $matchedSlot->booking;
                if (in_array($booking->status, [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])) {
                    $status = 'booked';
                } elseif ($booking->status === Booking::STATUS_PENDING_PAYMENT) {
                    if (Carbon::now()->lt($booking->expires_at)) {
                        $status = 'held';
                    } else {
                        // Stale held slot, considered available
                        $status = 'available';
                    }
                }
            }

            // Check if slot is already in the past
            $isPast = $slotStartLocal->isPast();

            $slots[] = [
                'court_id' => $court->id,
                'court_name' => $court->name,
                'date' => $dateString,
                'start_time' => $slotStartLocal->format('H:i'),
                'end_time' => $slotEndLocal->format('H:i'),
                'slot_start_iso' => $slotStartUtc->toIso8601String(),
                'slot_end_iso' => $slotEndUtc->toIso8601String(),
                'slot_start_local' => $slotStartLocal->format('Y-m-d H:i:s'),
                'price' => (float) $price,
                'status' => $status,
                'is_past' => $isPast,
                'is_bookable' => ($status === 'available' && !$isPast),
            ];

            $currentCursor->addMinutes($slotDuration);
        }

        return [
            'court' => [
                'id' => $court->id,
                'name' => $court->name,
                'sport_type' => $court->sport_type,
                'is_active' => $court->is_active,
                'venue' => [
                    'id' => $venue->id,
                    'name' => $venue->name,
                    'city' => $venue->city,
                    'dp_percent' => $venue->dp_percent,
                    'cancel_free_hours' => $venue->cancel_free_hours,
                    'cancel_refund_percent' => $venue->cancel_refund_percent,
                ],
            ],
            'date' => $dateString,
            'slot_count' => count($slots),
            'slots' => $slots,
        ];
    }

    /**
     * Calculate slot price according to day type and time.
     */
    public function calculateSlotPrice(Court $court, Carbon $timeLocal, bool $isWeekend): float
    {
        $dayType = $isWeekend ? 'weekend' : 'weekday';
        $timeStr = $timeLocal->format('H:i:s');

        // Check specific court price matching day_type and time window
        $matchedPrice = $court->courtPrices
            ->first(function ($cp) use ($dayType, $timeStr) {
                $matchesDay = ($cp->day_type === 'all' || $cp->day_type === $dayType);
                $matchesTime = ($timeStr >= $cp->start_time && $timeStr < $cp->end_time);
                return $matchesDay && $matchesTime;
            });

        if ($matchedPrice) {
            return (float) $matchedPrice->price;
        }

        // Fallback default court price
        $anyPrice = $court->courtPrices->first();
        return $anyPrice ? (float) $anyPrice->price : 100000.00;
    }

    /**
     * Create atomic booking with multi-layer concurrency protection:
     * 1. Cache Lock per slot
     * 2. DB Transaction with lockForUpdate
     * 3. DB Unique index constraint on (court_id, slot_start)
     */
    public function createBooking(User $user, Court $court, array $slotStartsUtc, ?string $notes = null): Booking
    {
        if (empty($slotStartsUtc)) {
            throw new Exception("Minimal satu slot harus dipilih untuk booking.");
        }

        // Sort slot starts chronologically
        sort($slotStartsUtc);

        $court->loadMissing(['venue', 'courtPrices', 'operatingHours']);
        $venue = $court->venue;

        if (!$court->is_active) {
            throw new Exception("Lapangan saat ini sedang tidak aktif.");
        }

        // Clean up expired bookings first
        $this->expireStalePendingBookings();

        // 1. Acquire atomic distributed locks for all requested slots
        $lockKeys = [];
        $acquiredLocks = [];

        foreach ($slotStartsUtc as $slotStartStr) {
            $ts = Carbon::parse($slotStartStr)->timestamp;
            $lockKey = "court:{$court->id}:slot:{$ts}";
            $lock = Cache::lock($lockKey, 5); // 5 seconds lock

            if (!$lock->get()) {
                // Release any previously acquired locks
                foreach ($acquiredLocks as $l) {
                    $l->release();
                }
                throw new Exception("Slot yang Anda pilih sedang dalam proses booking oleh pengguna lain. Silakan coba lagi beberapa saat.");
            }
            $acquiredLocks[] = $lock;
            $lockKeys[] = $lockKey;
        }

        try {
            return DB::transaction(function () use ($user, $court, $venue, $slotStartsUtc, $notes) {
                // 2. Lock court record to synchronize writes
                Court::where('id', $court->id)->lockForUpdate()->first();

                // Check for existing slots or conflicts in booking_slots
                $existingSlots = BookingSlot::where('court_id', $court->id)
                    ->whereIn('slot_start', array_map(fn($s) => Carbon::parse($s)->toDateTimeString(), $slotStartsUtc))
                    ->lockForUpdate()
                    ->get();

                if ($existingSlots->isNotEmpty()) {
                    throw new Exception("Satu atau lebih slot yang dipilih sudah terisi atau di-hold. Silakan pilih slot lain.");
                }

                // Check for schedule blocks
                $firstSlot = Carbon::parse($slotStartsUtc[0]);
                $lastSlot = Carbon::parse(end($slotStartsUtc))->addMinutes(60);

                $hasBlock = ScheduleBlock::where('court_id', $court->id)
                    ->where(function ($q) use ($firstSlot, $lastSlot) {
                        $q->whereBetween('start_at', [$firstSlot, $lastSlot])
                          ->orWhereBetween('end_at', [$firstSlot, $lastSlot]);
                    })
                    ->exists();

                if ($hasBlock) {
                    throw new Exception("Slot terpilih berbenturan dengan jadwal perawatan atau kegiatan venue.");
                }

                // Check consecutive slots if multiple
                if (count($slotStartsUtc) > 1) {
                    for ($i = 0; $i < count($slotStartsUtc) - 1; $i++) {
                        $curr = Carbon::parse($slotStartsUtc[$i]);
                        $next = Carbon::parse($slotStartsUtc[$i + 1]);
                        if ($curr->copy()->addMinutes(60)->ne($next)) {
                            throw new Exception("Slot booking harus berurutan pada lapangan yang sama.");
                        }
                    }
                }

                // Calculate pricing
                $totalPrice = 0;
                $slotData = [];

                foreach ($slotStartsUtc as $slotStartStr) {
                    $startUtc = Carbon::parse($slotStartStr);
                    $endUtc = $startUtc->copy()->addMinutes(60);
                    $startLocal = $startUtc->copy()->setTimezone('Asia/Jakarta');

                    $price = $this->calculateSlotPrice($court, $startLocal, $startLocal->isWeekend());
                    $totalPrice += $price;

                    $slotData[] = [
                        'court_id' => $court->id,
                        'slot_start' => $startUtc->toDateTimeString(),
                        'slot_end' => $endUtc->toDateTimeString(),
                        'price' => $price,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // DP calculation based on venue dp_percent
                $dpPercent = max(10, min(100, (int) $venue->dp_percent));
                $dpAmount = round(($totalPrice * $dpPercent) / 100, 2);

                // Booking code generation
                $bookingCode = 'BK-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                // 3. Create booking record with 15-minute expiration hold
                $booking = Booking::create([
                    'code' => $bookingCode,
                    'user_id' => $user->id,
                    'court_id' => $court->id,
                    'total_price' => $totalPrice,
                    'dp_amount' => $dpAmount,
                    'status' => Booking::STATUS_PENDING_PAYMENT,
                    'expires_at' => Carbon::now()->addMinutes(15),
                    'cancel_free_hours_snapshot' => $venue->cancel_free_hours,
                    'cancel_refund_percent_snapshot' => $venue->cancel_refund_percent,
                    'notes' => $notes,
                ]);

                // Insert booking slots
                foreach ($slotData as &$slot) {
                    $slot['booking_id'] = $booking->id;
                    BookingSlot::create($slot);
                }

                // Dispatch notification for new booking
                $this->notificationService->notifyBookingCreated($booking);

                return $booking->load(['court.venue', 'bookingSlots', 'user']);
            });
        } finally {
            // Release all distributed locks
            foreach ($acquiredLocks as $lock) {
                try {
                    $lock->release();
                } catch (\Throwable $e) {
                    // Ignore release errors
                }
            }
        }
    }

    /**
     * Cancel booking according to venue cancellation policy.
     */
    public function cancelBooking(Booking $booking, User $user, ?string $reason = null): Booking
    {
        // Check permission
        if ($booking->user_id !== $user->id && !$user->isAdmin() && $user->id !== $booking->court->venue->owner_id) {
            throw new Exception("Anda tidak memiliki izin untuk membatalkan booking ini.");
        }

        if (!in_array($booking->status, [Booking::STATUS_PENDING_PAYMENT, Booking::STATUS_CONFIRMED])) {
            throw new Exception("Booking dengan status {$booking->status} tidak dapat dibatalkan.");
        }

        return DB::transaction(function () use ($booking, $reason) {
            $earliestSlot = $booking->bookingSlots()->orderBy('slot_start', 'asc')->first();
            $refundAmount = 0;

            if ($booking->status === Booking::STATUS_CONFIRMED && $earliestSlot) {
                $earliestSlotTime = Carbon::parse($earliestSlot->slot_start);
                $freeHours = $booking->cancel_free_hours_snapshot;
                $refundPercent = $booking->cancel_refund_percent_snapshot;

                // If cancelled before freeHours cutoff
                if (Carbon::now()->diffInHours($earliestSlotTime, false) >= $freeHours) {
                    $refundAmount = round(($booking->dp_amount * $refundPercent) / 100, 2);
                }
            }

            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancelled_at' => Carbon::now(),
                'refund_amount' => $refundAmount,
                'cancellation_reason' => $reason ?: 'Dibatalkan oleh pengguna',
            ]);

            // Release slots so other users can book them
            BookingSlot::where('booking_id', $booking->id)->delete();

            $this->notificationService->notifyBookingCancelled($booking);

            return $booking->fresh(['court.venue', 'user', 'payments']);
        });
    }

    /**
     * Mark booking as completed or no-show (by pengelola).
     */
    public function updateOperationalStatus(Booking $booking, string $newStatus): Booking
    {
        if (!in_array($newStatus, [Booking::STATUS_COMPLETED, Booking::STATUS_NO_SHOW])) {
            throw new Exception("Status operasional tidak valid: {$newStatus}");
        }

        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            throw new Exception("Hanya booking yang telah berstatus CONFIRMED yang dapat diubah ke {$newStatus}.");
        }

        $booking->update(['status' => $newStatus]);
        return $booking;
    }

    /**
     * Expire stale pending bookings (ran by scheduler and on-demand).
     */
    public function expireStalePendingBookings(): int
    {
        $expiredBookings = Booking::where('status', Booking::STATUS_PENDING_PAYMENT)
            ->where('expires_at', '<=', Carbon::now())
            ->get();

        $count = 0;
        foreach ($expiredBookings as $booking) {
            DB::transaction(function () use ($booking) {
                $booking->update(['status' => Booking::STATUS_EXPIRED]);
                // Delete slots so they are free for other bookers
                BookingSlot::where('booking_id', $booking->id)->delete();
                $this->notificationService->notifyBookingExpired($booking);
            });
            $count++;
        }

        return $count;
    }
}
