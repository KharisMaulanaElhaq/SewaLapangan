<?php

namespace App\Http\Controllers\Api\v1\Pengelola;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourtPriceRequest;
use App\Http\Requests\StoreCourtRequest;
use App\Http\Requests\StoreScheduleBlockRequest;
use App\Http\Resources\CourtPriceResource;
use App\Http\Resources\CourtResource;
use App\Http\Resources\ScheduleBlockResource;
use App\Models\Booking;
use App\Models\Court;
use App\Models\CourtPrice;
use App\Models\OperatingHour;
use App\Models\ScheduleBlock;
use App\Models\Venue;
use App\Services\BookingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManageCourtController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Store a new court.
     */
    public function store(StoreCourtRequest $request): JsonResponse
    {
        $user = $request->user();
        $venue = Venue::where('owner_id', $user->id)->findOrFail($request->venue_id);

        $court = Court::create([
            'venue_id' => $venue->id,
            'name' => $request->name,
            'sport_type' => $request->sport_type,
            'is_active' => $request->input('is_active', true),
        ]);

        // Seed default operating hours (Monday to Sunday 08:00 - 23:00)
        for ($day = 0; $day <= 6; $day++) {
            OperatingHour::create([
                'court_id' => $court->id,
                'day_of_week' => $day,
                'open_time' => '08:00:00',
                'close_time' => '23:00:00',
                'slot_duration_minutes' => 60,
            ]);
        }

        // Seed default standard price
        CourtPrice::create([
            'court_id' => $court->id,
            'day_type' => 'all',
            'start_time' => '08:00:00',
            'end_time' => '23:00:00',
            'price' => 100000,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lapangan berhasil ditambahkan beserta jam operasional default.',
            'data' => new CourtResource($court->load(['operatingHours', 'courtPrices'])),
        ], 200);
    }

    /**
     * Update court details.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $court = Court::whereHas('venue', function ($q) use ($user) {
            $q->where('owner_id', $user->id);
        })->findOrFail($id);

        $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sport_type' => ['sometimes', 'required', 'string', 'in:futsal,badminton,basketball,tennis,mini_soccer,volleyball'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $court->update($request->only(['name', 'sport_type', 'is_active']));

        return response()->json([
            'status' => 'success',
            'message' => 'Lapangan berhasil diperbarui.',
            'data' => new CourtResource($court),
        ]);
    }

    /**
     * Delete court.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $court = Court::whereHas('venue', function ($q) use ($user) {
            $q->where('owner_id', $user->id);
        })->findOrFail($id);

        $court->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Lapangan berhasil dihapus.',
        ]);
    }

    /**
     * Add or update court price tier.
     */
    public function storePrice(StoreCourtPriceRequest $request): JsonResponse
    {
        $user = $request->user();
        $court = Court::whereHas('venue', function ($q) use ($user) {
            $q->where('owner_id', $user->id);
        })->findOrFail($request->court_id);

        $price = CourtPrice::create([
            'court_id' => $court->id,
            'day_type' => $request->day_type,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'price' => $request->price,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Aturan harga berhasil ditambahkan.',
            'data' => new CourtPriceResource($price),
        ], 200);
    }

    /**
     * Delete price tier.
     */
    public function destroyPrice(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $price = CourtPrice::whereHas('court.venue', function ($q) use ($user) {
            $q->where('owner_id', $user->id);
        })->findOrFail($id);

        $price->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Aturan harga berhasil dihapus.',
        ]);
    }

    /**
     * Add schedule block (maintenance / event).
     */
    public function storeBlock(StoreScheduleBlockRequest $request): JsonResponse
    {
        $user = $request->user();
        $court = Court::whereHas('venue', function ($q) use ($user) {
            $q->where('owner_id', $user->id);
        })->findOrFail($request->court_id);

        $block = ScheduleBlock::create([
            'court_id' => $court->id,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'reason' => $request->reason,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal blokir (maintenance/event) berhasil dibuat.',
            'data' => new ScheduleBlockResource($block),
        ], 200);
    }

    /**
     * Delete schedule block.
     */
    public function destroyBlock(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $block = ScheduleBlock::whereHas('court.venue', function ($q) use ($user) {
            $q->where('owner_id', $user->id);
        })->findOrFail($id);

        $block->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Blokir jadwal berhasil dihapus.',
        ]);
    }

    /**
     * Update operational booking status (COMPLETED or NO_SHOW).
     */
    public function updateBookingStatus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $booking = Booking::whereHas('court.venue', function ($q) use ($user) {
            if (!$user->isAdmin()) {
                $q->where('owner_id', $user->id);
            }
        })->findOrFail($id);

        $request->validate([
            'status' => ['required', 'string', 'in:COMPLETED,NO_SHOW'],
        ]);

        try {
            $updated = $this->bookingService->updateOperationalStatus($booking, $request->status);

            return response()->json([
                'status' => 'success',
                'message' => "Status booking berhasil diubah menjadi {$request->status}.",
                'data' => $updated,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
