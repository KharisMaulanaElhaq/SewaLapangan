<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\VenueResource;
use App\Models\CourtPrice;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VenueController extends Controller
{
    /**
     * Allowed sorting parameters for GET /api/v1/venues (OpenAPI v1 whitelist).
     */
    private const ALLOWED_SORTS = [
        'name',
        '-name',
        'created_at',
        '-created_at',
        'starting_price',
        '-starting_price',
    ];

    /**
     * Search and list public venues with filters (sport_type, city, q, min_price, max_price, sort, page, per_page).
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'city' => ['nullable', 'string', 'max:100'],
            'sport_type' => ['nullable', 'string', 'in:futsal,badminton,basketball,mini_soccer'],
            'sport' => ['nullable', 'string', 'in:futsal,badminton,basketball,mini_soccer'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'string', 'in:' . implode(',', self::ALLOWED_SORTS)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter pencarian atau pengurutan katalog venue tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $startingPriceSubquery = CourtPrice::selectRaw('MIN(court_prices.price)')
            ->join('courts', 'courts.id', '=', 'court_prices.court_id')
            ->whereColumn('courts.venue_id', 'venues.id')
            ->where('courts.is_active', true);

        $query = Venue::query()
            ->select('venues.*')
            ->addSelect(['starting_price' => $startingPriceSubquery])
            ->with(['courts' => function ($q) {
                $q->where('is_active', true)->with('courtPrices');
            }]);

        // Filter by City
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Filter by Sport Type (supports OpenAPI v1 `sport_type` and legacy `sport`)
        $sportType = $request->input('sport_type', $request->input('sport'));
        if (!empty($sportType)) {
            $query->whereHas('courts', function ($q) use ($sportType) {
                $q->where('sport_type', $sportType)->where('is_active', true);
            });
        }

        // Search Keyword (Venue Name, Address, or City)
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('address', 'like', '%' . $request->q . '%')
                  ->orWhere('city', 'like', '%' . $request->q . '%');
            });
        }

        // Filter by Price range on active courts
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('courts', function ($courtQuery) use ($request) {
                $courtQuery->where('is_active', true)->whereHas('courtPrices', function ($priceQuery) use ($request) {
                    if ($request->filled('min_price')) {
                        $priceQuery->where('price', '>=', (float) $request->min_price);
                    }
                    if ($request->filled('max_price')) {
                        $priceQuery->where('price', '<=', (float) $request->max_price);
                    }
                });
            });
        }

        // Whitelist Sorting (default: name ascending per OpenAPI v1)
        $sort = $request->input('sort', 'name');
        match ($sort) {
            'name' => $query->orderBy('venues.name', 'asc'),
            '-name' => $query->orderBy('venues.name', 'desc'),
            'created_at' => $query->orderBy('venues.created_at', 'asc'),
            '-created_at' => $query->orderBy('venues.created_at', 'desc'),
            'starting_price' => $query->orderByRaw('starting_price IS NULL ASC, starting_price ASC'),
            '-starting_price' => $query->orderByRaw('starting_price IS NULL ASC, starting_price DESC'),
            default => $query->orderBy('venues.name', 'asc'),
        };

        $perPage = (int) $request->input('per_page', 15);
        $venues = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'status' => 'success',
            'data' => VenueResource::collection($venues),
            'links' => [
                'first' => $venues->url(1),
                'last' => $venues->url($venues->lastPage()),
                'prev' => $venues->previousPageUrl(),
                'next' => $venues->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $venues->currentPage(),
                'per_page' => $venues->perPage(),
                'from' => $venues->firstItem(),
                'to' => $venues->lastItem(),
                'total' => $venues->total(),
                'last_page' => $venues->lastPage(),
            ],
        ]);
    }

    /**
     * Show single venue details.
     */
    public function show(string|int $id): JsonResponse
    {
        $startingPriceSubquery = CourtPrice::selectRaw('MIN(court_prices.price)')
            ->join('courts', 'courts.id', '=', 'court_prices.court_id')
            ->whereColumn('courts.venue_id', 'venues.id')
            ->where('courts.is_active', true);

        $venue = Venue::query()
            ->select('venues.*')
            ->addSelect(['starting_price' => $startingPriceSubquery])
            ->with([
                'courts' => function ($q) {
                    $q->where('is_active', true)->with(['courtPrices', 'operatingHours']);
                },
                'owner',
            ])
            ->where('id', $id)
            ->orWhere('slug', $id)
            ->first();

        if (!$venue) {
            return response()->json([
                'status' => 'error',
                'message' => 'Venue olahraga tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new VenueResource($venue),
        ]);
    }
}

