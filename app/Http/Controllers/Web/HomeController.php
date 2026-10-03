<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Venue::with(['courts' => function ($q) {
            $q->where('is_active', true)->with('courtPrices');
        }]);

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('sport')) {
            $query->whereHas('courts', function ($q) use ($request) {
                $q->where('sport_type', $request->sport)->where('is_active', true);
            });
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('address', 'like', '%' . $request->q . '%')
                  ->orWhere('city', 'like', '%' . $request->q . '%');
            });
        }

        $venues = $query->latest()->get();
        $cities = Venue::select('city')->distinct()->pluck('city');
        $sports = Court::select('sport_type')->distinct()->pluck('sport_type');

        return view('home', compact('venues', 'cities', 'sports'));
    }
}
