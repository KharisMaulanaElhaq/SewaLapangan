<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Venue;
use App\Services\BookingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Show venue detail and interactive slot calendar.
     */
    public function show(Request $request, string|int $id): View
    {
        $venue = Venue::with([
            'courts' => function ($q) {
                $q->where('is_active', true)->with(['courtPrices', 'operatingHours']);
            },
            'owner'
        ])->where('id', $id)->orWhere('slug', $id)->firstOrFail();

        $selectedCourtId = $request->query('court_id', $venue->courts->first()?->id);
        $selectedCourt = $venue->courts->firstWhere('id', $selectedCourtId) ?: $venue->courts->first();

        $selectedDate = $request->query('date', Carbon::now('Asia/Jakarta')->toDateString());

        $availability = null;
        if ($selectedCourt) {
            $availability = $this->bookingService->getAvailability($selectedCourt, $selectedDate);
        }

        return view('venues.show', compact('venue', 'selectedCourt', 'selectedDate', 'availability'));
    }

    /**
     * Handle booking submission from web form.
     */
    public function book(Request $request): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'Silakan login terlebih dahulu untuk melakukan booking lapangan.');
        }

        $validated = $request->validate([
            'court_id' => ['required', 'exists:courts,id'],
            'slots' => ['required', 'array', 'min:1'],
            'slots.*' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $court = Court::with('venue')->findOrFail($validated['court_id']);

        try {
            $booking = $this->bookingService->createBooking(
                user: Auth::user(),
                court: $court,
                slotStartsUtc: $validated['slots'],
                notes: $validated['notes'] ?? null
            );

            return redirect()->route('booking.checkout', $booking->id)
                ->with('success', 'Booking berhasil dibuat! Slot Anda telah dikunci selama 15 menit. Silakan selesaikan pembayaran DP.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
