<?php

namespace App\Http\Controllers\Api\v1\Pengelola;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenueRequest;
use App\Http\Resources\VenueResource;
use App\Models\Booking;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ManageVenueController extends Controller
{
    /**
     * List all venues owned by authenticated pengelola.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Venue::with(['courts.courtPrices', 'courts.operatingHours'])->latest();

        if (!$user->isAdmin()) {
            $query->where('owner_id', $user->id);
        }

        $venues = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => VenueResource::collection($venues),
        ]);
    }

    /**
     * Store new venue (POST /api/v1/manage/venues).
     * Returns 201 Created with Location header per OpenAPI v1.
     */
    public function store(StoreVenueRequest $request): JsonResponse
    {
        $user = $request->user();
        $baseSlug = Str::slug($request->name);
        $slug = $baseSlug;
        $counter = 1;
        while (Venue::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $venue = Venue::create([
            'owner_id' => $user->id,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'address' => $request->address,
            'city' => $request->city,
            'phone' => $request->phone,
            'dp_percent' => $request->dp_percent,
            'cancel_free_hours' => $request->cancel_free_hours,
            'cancel_refund_percent' => $request->cancel_refund_percent,
            'image_url' => $request->image_url,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Venue berhasil ditambahkan.',
            'data' => new VenueResource($venue),
        ], 201)->header('Location', '/api/v1/venues/' . $venue->id);
    }

    /**
     * Update venue details (PUT /api/v1/manage/venues/{id}).
     */
    public function update(StoreVenueRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $venue = Venue::find($id);

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
                'message' => 'Anda tidak memiliki wewenang untuk mengubah venue ini.',
                'code' => 'FORBIDDEN_ACCESS',
            ], 403);
        }

        $venue->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data venue berhasil diperbarui.',
            'data' => new VenueResource($venue->fresh()),
        ]);
    }

    /**
     * Delete venue (DELETE /api/v1/manage/venues/{id}).
     * Protects against deleting a venue that still has active bookings (409 Conflict).
     */
    public function destroy(Request $request, int $id): JsonResponse|Response
    {
        $user = $request->user();
        $venue = Venue::find($id);

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
                'message' => 'Anda tidak memiliki wewenang untuk menghapus venue ini.',
                'code' => 'FORBIDDEN_ACCESS',
            ], 403);
        }

        // Proteksi 409 Conflict: Cek jika masih ada booking aktif di lapangan milik venue ini
        $hasActiveBookings = Booking::whereHas('court', function ($query) use ($venue) {
            $query->where('venue_id', $venue->id);
        })->whereIn('status', [
            Booking::STATUS_PENDING_PAYMENT,
            Booking::STATUS_CONFIRMED,
        ])->exists();

        if ($hasActiveBookings) {
            return response()->json([
                'status' => 'error',
                'message' => 'Venue tidak dapat dihapus karena masih memiliki pesanan booking aktif.',
                'code' => 'ACTIVE_BOOKINGS_EXIST',
            ], 409);
        }

        $venue->delete();

        return response()->noContent();
    }
}
