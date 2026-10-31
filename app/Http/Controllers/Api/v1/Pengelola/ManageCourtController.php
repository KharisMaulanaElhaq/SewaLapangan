<?php

namespace App\Http\Controllers\Api\v1\Pengelola;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourtPriceRequest;
use App\Http\Requests\StoreCourtRequest;
use App\Http\Requests\StoreScheduleBlockRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\CourtPriceResource;
use App\Http\Resources\CourtResource;
use App\Http\Resources\ScheduleBlockResource;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\CourtPrice;
use App\Models\OperatingHour;
use App\Models\ScheduleBlock;
use App\Models\Venue;
use App\Services\BookingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ManageCourtController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Store a new court (POST /api/v1/manage/courts).
     * Returns 201 Created per OpenAPI v1.
     */
    public function store(StoreCourtRequest $request): JsonResponse
    {
        $user = $request->user();
        $venue = Venue::find($request->venue_id);

        if (!$venue) {
            return response()->json([
                'status' => 'error',
                'message' => 'Venue olahraga tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        if ($venue->owner_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki wewenang untuk menambahkan lapangan pada venue ini.',
                'code' => 'FORBIDDEN_ACCESS',
            ], 403);
        }

        $court = Court::create([
            'venue_id' => $venue->id,
            'name' => $request->name,
            'sport_type' => $request->sport_type,
            'is_active' => $request->input('is_active', true),
        ]);

        // Seed default operating hours (Sunday to Saturday 08:00 - 23:00)
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
            'data' => new CourtResource($court->load(['venue', 'operatingHours', 'courtPrices'])),
        ], 201)->header('Location', '/api/v1/courts/' . $court->id);
    }

    /**
     * Update court details completely (PUT /api/v1/manage/courts/{id}).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $court = Court::with('venue')->find($id);

        if (!$court) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lapangan tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        if ($court->venue->owner_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki wewenang untuk mengubah lapangan ini.',
                'code' => 'FORBIDDEN_ACCESS',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'venue_id' => ['sometimes', 'integer', 'exists:venues,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sport_type' => ['sometimes', 'required', 'string', 'in:futsal,badminton,basketball,tennis,mini_soccer,volleyball'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data yang dikirim tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $court->update($request->only(['name', 'sport_type', 'is_active']));

        return response()->json([
            'status' => 'success',
            'message' => 'Lapangan berhasil diperbarui.',
            'data' => new CourtResource($court->fresh(['venue'])),
        ]);
    }

    /**
     * Toggle court active status partially (PATCH /api/v1/manage/courts/{id}).
     */
    public function patchStatus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $court = Court::with('venue')->find($id);

        if (!$court) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lapangan tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        if ($court->venue->owner_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki wewenang untuk mengubah status lapangan ini.',
                'code' => 'FORBIDDEN_ACCESS',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'is_active' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Status keaktifan lapangan (is_active) wajib berupa boolean.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $court->update([
            'is_active' => (bool) $request->boolean('is_active'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status keaktifan lapangan berhasil diubah.',
            'data' => new CourtResource($court->fresh(['venue'])),
        ], 200);
    }

    /**
     * Delete court (DELETE /api/v1/manage/courts/{id}).
     * Protects against deleting a court that still has active bookings (409 Conflict).
     */
    public function destroy(Request $request, int $id): JsonResponse|Response
    {
        $user = $request->user();
        $court = Court::with('venue')->find($id);

        if (!$court) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lapangan tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        if ($court->venue->owner_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki wewenang untuk menghapus lapangan ini.',
                'code' => 'FORBIDDEN_ACCESS',
            ], 403);
        }

        // Proteksi 409 Conflict: Cek jika masih ada booking aktif pada lapangan ini
        $hasActiveBookings = Booking::where('court_id', $court->id)
            ->whereIn('status', [
                Booking::STATUS_PENDING_PAYMENT,
                Booking::STATUS_CONFIRMED,
            ])
            ->exists();

        if ($hasActiveBookings) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lapangan tidak dapat dihapus karena masih memiliki pesanan booking aktif.',
                'code' => 'ACTIVE_BOOKINGS_EXIST',
            ], 409);
        }

        $court->delete();

        return response()->noContent();
    }

    /**
     * Add court price tier (POST /api/v1/manage/prices).
     * Returns 201 Created per OpenAPI v1.
     */
    public function storePrice(StoreCourtPriceRequest $request): JsonResponse
    {
        $user = $request->user();
        $court = Court::with('venue')->find($request->court_id);

        if (!$court) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lapangan tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        if ($court->venue->owner_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki wewenang untuk mengatur tarif lapangan ini.',
                'code' => 'FORBIDDEN_ACCESS',
            ], 403);
        }

        $dayType = $request->day_type === 'all_days' ? 'all' : $request->day_type;

        $price = CourtPrice::create([
            'court_id' => $court->id,
            'day_type' => $dayType,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'price' => $request->price,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Aturan harga berhasil ditambahkan.',
            'data' => new CourtPriceResource($price),
        ], 201);
    }

    /**
     * Delete price tier (DELETE /api/v1/manage/prices/{id}).
     * Returns 204 No Content per OpenAPI v1.
     */
    public function destroyPrice(Request $request, int $id): JsonResponse|Response
    {
        $user = $request->user();
        $price = CourtPrice::with('court.venue')->find($id);

        if (!$price) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aturan tarif sewa tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        if ($price->court->venue->owner_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki wewenang untuk menghapus aturan tarif ini.',
                'code' => 'FORBIDDEN_ACCESS',
            ], 403);
        }

        $price->delete();

        return response()->noContent();
    }

    /**
     * Add schedule block for maintenance or tournament (POST /api/v1/manage/blocks).
     * Returns 201 Created, or 409 Conflict if overlapping with active booking slots.
     */
    public function storeBlock(StoreScheduleBlockRequest $request): JsonResponse
    {
        $user = $request->user();
        $court = Court::with('venue')->find($request->court_id);

        if (!$court) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lapangan tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        if ($court->venue->owner_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki wewenang untuk memblokir jadwal lapangan ini.',
                'code' => 'FORBIDDEN_ACCESS',
            ], 403);
        }

        $startAtUtc = Carbon::parse($request->start_at)->utc();
        $endAtUtc = Carbon::parse($request->end_at)->utc();

        $hasConflictingSlot = BookingSlot::where('court_id', $court->id)
            ->where('slot_start', '<', $endAtUtc)
            ->where('slot_end', '>', $startAtUtc)
            ->whereHas('booking', function ($q) {
                $q->whereIn('status', [
                    Booking::STATUS_PENDING_PAYMENT,
                    Booking::STATUS_CONFIRMED,
                ]);
            })
            ->exists();

        if ($hasConflictingSlot) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rentang waktu bentrok dengan pesanan booking penyewa yang sedang aktif.',
                'code' => 'SCHEDULE_BLOCK_CONFLICT',
            ], 409);
        }

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
        ], 201);
    }

    /**
     * Delete schedule block (DELETE /api/v1/manage/blocks/{id}).
     * Returns 204 No Content per OpenAPI v1.
     */
    public function destroyBlock(Request $request, int $id): JsonResponse|Response
    {
        $user = $request->user();
        $block = ScheduleBlock::with('court.venue')->find($id);

        if (!$block) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pemblokiran jadwal tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        if ($block->court->venue->owner_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki wewenang untuk menghapus pemblokiran jadwal ini.',
                'code' => 'FORBIDDEN_ACCESS',
            ], 403);
        }

        $block->delete();

        return response()->noContent();
    }

    /**
     * Update operational booking status (COMPLETED or NO_SHOW) (PATCH /api/v1/manage/bookings/{id}/status).
     */
    public function updateBookingStatus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $booking = Booking::with(['court.venue', 'bookingSlots', 'payments', 'user'])->find($id);

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi booking tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        if ($booking->court->venue->owner_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki wewenang untuk mengubah status booking ini.',
                'code' => 'FORBIDDEN_ACCESS',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => ['required', 'string', 'in:COMPLETED,NO_SHOW'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Status operasional harus bernilai COMPLETED atau NO_SHOW.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updated = $this->bookingService->updateOperationalStatus($booking, $request->status);

            return response()->json([
                'status' => 'success',
                'message' => "Status booking berhasil diubah menjadi {$request->status}.",
                'data' => new BookingResource($updated->load(['court.venue', 'bookingSlots', 'payments', 'user'])),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
