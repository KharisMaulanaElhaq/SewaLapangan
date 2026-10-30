<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\CourtPrice;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
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

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        $sportType = $request->input('sport_type', $request->input('sport'));
        if (!empty($sportType)) {
            $query->whereHas('courts', function ($q) use ($sportType) {
                $q->where('sport_type', $sportType)->where('is_active', true);
            });
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('address', 'like', '%' . $request->q . '%')
                  ->orWhere('city', 'like', '%' . $request->q . '%');
            });
        }

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

        $venues = $query->get();
        $cities = Venue::select('city')->distinct()->pluck('city');
        $sports = Court::select('sport_type')->distinct()->pluck('sport_type');

        return view('home', compact('venues', 'cities', 'sports'));
    }
}

