<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\VenueResource;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    /**
     * Search and list public venues with filters (sport, city, date, price).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Venue::with(['courts' => function ($q) {
            $q->where('is_active', true)->with('courtPrices');
        }]);

        // Filter by City
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Filter by Sport Type
        if ($request->filled('sport')) {
            $query->whereHas('courts', function ($q) use ($request) {
                $q->where('sport_type', $request->sport)->where('is_active', true);
            });
        }

        // Search Keyword (Venue Name or Address)
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('address', 'like', '%' . $request->q . '%')
                  ->orWhere('city', 'like', '%' . $request->q . '%');
            });
        }

        // Filter by Price range
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('courts.courtPrices', function ($q) use ($request) {
                if ($request->filled('min_price')) {
                    $q->where('price', '>=', (float) $request->min_price);
                }
                if ($request->filled('max_price')) {
                    $q->where('price', '<=', (float) $request->max_price);
                }
            });
        }

        $venues = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => VenueResource::collection($venues),
            'meta' => [
                'current_page' => $venues->currentPage(),
                'last_page' => $venues->lastPage(),
                'total' => $venues->total(),
            ],
        ]);
    }

    /**
     * Show single venue details.
     */
    public function show(string|int $id): JsonResponse
    {
        $venue = Venue::with([
            'courts' => function ($q) {
                $q->where('is_active', true)->with(['courtPrices', 'operatingHours']);
            },
            'owner'
        ])->where('id', $id)->orWhere('slug', $id)->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => new VenueResource($venue),
        ]);
    }
}
