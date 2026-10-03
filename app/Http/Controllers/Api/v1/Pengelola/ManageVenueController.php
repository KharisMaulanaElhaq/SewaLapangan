<?php

namespace App\Http\Controllers\Api\v1\Pengelola;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenueRequest;
use App\Http\Resources\VenueResource;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ManageVenueController extends Controller
{
    /**
     * List all venues owned by authenticated pengelola.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $venues = Venue::where('owner_id', $user->id)
            ->with(['courts.courtPrices', 'courts.operatingHours'])
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => VenueResource::collection($venues),
        ]);
    }

    /**
     * Store new venue.
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
        ], 201);
    }

    /**
     * Update venue details.
     */
    public function update(StoreVenueRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $venue = Venue::where('owner_id', $user->id)->findOrFail($id);

        $venue->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data venue berhasil diperbarui.',
            'data' => new VenueResource($venue),
        ]);
    }

    /**
     * Delete venue.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $venue = Venue::where('owner_id', $user->id)->findOrFail($id);

        $venue->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Venue berhasil dihapus.',
        ]);
    }
}
