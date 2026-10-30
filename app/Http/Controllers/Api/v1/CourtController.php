<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourtResource;
use App\Models\Court;
use App\Models\Venue;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourtController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * List active courts belonging to a specific venue (GET /api/v1/venues/{id}/courts).
     */
    public function byVenue(string|int $id): JsonResponse
    {
        $venue = Venue::where('id', $id)->orWhere('slug', $id)->first();

        if (!$venue) {
            return response()->json([
                'status' => 'error',
                'message' => 'Venue olahraga tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        $courts = $venue->courts()
            ->where('is_active', true)
            ->with(['venue', 'courtPrices', 'operatingHours'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => CourtResource::collection($courts),
        ]);
    }

    /**
     * Get single court detail with parent venue relation (GET /api/v1/courts/{id}).
     */
    public function show(int $id): JsonResponse
    {
        $court = Court::with(['venue', 'courtPrices', 'operatingHours', 'scheduleBlocks'])->find($id);

        if (!$court) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lapangan tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new CourtResource($court),
        ]);
    }

    /**
     * Get pricing rules for a specific court (GET /api/v1/courts/{id}/prices).
     */
    public function prices(int $id): JsonResponse
    {
        $court = Court::with('courtPrices')->find($id);

        if (!$court) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lapangan tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        $prices = $court->courtPrices->map(fn ($price) => [
            'id' => $price->id,
            'court_id' => (int) $price->court_id,
            'day_type' => $price->day_type === 'all' ? 'all_days' : $price->day_type,
            'start_time' => $price->start_time,
            'end_time' => $price->end_time,
            'price' => (float) $price->price,
            'created_at' => $price->created_at?->toIso8601String(),
        ])->values();

        return response()->json([
            'status' => 'success',
            'data' => $prices,
        ]);
    }

    /**
     * Get 7-day operating hours for a specific court (GET /api/v1/courts/{id}/operating-hours).
     */
    public function operatingHours(int $id): JsonResponse
    {
        $court = Court::with('operatingHours')->find($id);

        if (!$court) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lapangan tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        $hours = $court->operatingHours
            ->sortBy('day_of_week')
            ->values()
            ->map(fn ($hour) => [
                'id' => $hour->id,
                'court_id' => (int) $hour->court_id,
                'day_of_week' => (int) $hour->day_of_week,
                'open_time' => $hour->open_time,
                'close_time' => $hour->close_time,
                'slot_duration_minutes' => (int) ($hour->slot_duration_minutes ?: 60),
            ]);

        return response()->json([
            'status' => 'success',
            'data' => $hours,
        ]);
    }

    /**
     * Get real-time slot availability for a court on a date (GET /api/v1/courts/{id}/availability).
     */
    public function availability(Request $request, int $id): JsonResponse
    {
        $court = Court::where('is_active', true)->find($id);

        if (!$court) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lapangan aktif tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        $dateString = (string) $request->input(
            'play_date',
            $request->input('date', Carbon::now('Asia/Jakarta')->toDateString())
        );

        // Date format validation (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format tanggal harus YYYY-MM-DD (contoh: 2026-10-25).',
                'errors' => [
                    'play_date' => ['Format tanggal harus YYYY-MM-DD (contoh: 2026-10-25).'],
                ],
            ], 422);
        }

        $availability = $this->bookingService->getAvailability($court, $dateString);

        $mappedSlots = collect($availability['slots'])->map(function (array $slot) {
            $upperStatus = match (strtolower($slot['status'])) {
                'available' => 'AVAILABLE',
                'held', 'hold' => 'HOLD',
                'booked' => 'BOOKED',
                'blocked' => 'BLOCKED',
                default => strtoupper($slot['status']),
            };

            return array_merge($slot, [
                'slot_start' => $slot['start_time'],
                'slot_end' => $slot['end_time'],
                'status' => $upperStatus,
                'price' => (float) $slot['price'],
            ]);
        })->values()->all();

        return response()->json([
            'status' => 'success',
            'court_id' => $court->id,
            'play_date' => $dateString,
            'court_name' => $court->name,
            'slots' => $mappedSlots,
            'data' => $availability,
        ]);
    }
}

