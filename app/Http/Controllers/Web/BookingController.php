<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\MidtransService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected MidtransService $midtransService
    ) {}

    /**
     * Checkout page for a booking with payment countdown and Snap gateway.
     */
    public function checkout(Request $request, int $id): View|RedirectResponse
    {
        if ($request->boolean('invoice') || $request->input('view') === 'invoice') {
            return $this->invoice($id);
        }

        $user = Auth::user();
        $booking = Booking::with(['court.venue', 'bookingSlots', 'payments', 'user'])->findOrFail($id);

        if ($booking->user_id !== $user->id && !$user->isAdmin() && $booking->court->venue->owner_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses ke booking ini.');
        }

        // Auto expire if past expiration
        if ($booking->status === Booking::STATUS_PENDING_PAYMENT && Carbon::now()->gte($booking->expires_at)) {
            $this->bookingService->expireStalePendingBookings();
            $booking->refresh();
        }

        $payment = null;
        if ($booking->status === Booking::STATUS_PENDING_PAYMENT) {
            try {
                $payment = $this->midtransService->createSnapTransaction($booking, 'DP');
            } catch (Exception $e) {
                // If token generation fails, display notification
            }
        } else {
            $payment = $booking->latestPayment;
        }

        return view('bookings.checkout', compact('booking', 'payment'));
    }

    /**
     * Display printable E-Ticket / official booking invoice (LUT-3).
     */
    public function invoice(int $id): View
    {
        $user = Auth::user();
        $booking = Booking::with(['court.venue', 'bookingSlots', 'payments', 'user'])->findOrFail($id);

        if ($booking->user_id !== $user->id && !$user->isAdmin() && $booking->court->venue->owner_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak E-Ticket booking ini.');
        }

        $payment = $booking->latestPayment;

        return view('bookings.invoice', compact('booking', 'payment'));
    }

    /**
     * Customer list of my bookings with search, date range filter, and sorting.
     */
    public function myBookings(Request $request): View
    {
        $user = Auth::user();
        $query = Booking::with(['court.venue', 'bookingSlots', 'payments'])
            ->where('user_id', $user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $query->where('code', 'like', '%' . trim($request->q) . '%');
        }

        if ($request->filled('date_from')) {
            $fromStartUtc = Carbon::parse($request->date_from, 'Asia/Jakarta')->startOfDay()->utc();
            $query->where(function ($q) use ($request, $fromStartUtc) {
                $q->whereHas('bookingSlots', function ($slotQuery) use ($request, $fromStartUtc) {
                    $slotQuery->where('slot_start', '>=', $fromStartUtc)
                              ->orWhereDate('slot_start', '>=', $request->date_from);
                })->orWhere(function ($fallback) use ($request) {
                    $fallback->doesntHave('bookingSlots')
                             ->whereDate('created_at', '>=', $request->date_from);
                });
            });
        }

        if ($request->filled('date_to')) {
            $toEndUtc = Carbon::parse($request->date_to, 'Asia/Jakarta')->endOfDay()->utc();
            $query->where(function ($q) use ($request, $toEndUtc) {
                $q->whereHas('bookingSlots', function ($slotQuery) use ($request, $toEndUtc) {
                    $slotQuery->where('slot_start', '<=', $toEndUtc)
                              ->orWhereDate('slot_start', '<=', $request->date_to);
                })->orWhere(function ($fallback) use ($request) {
                    $fallback->doesntHave('bookingSlots')
                             ->whereDate('created_at', '<=', $request->date_to);
                });
            });
        }

        $sort = $request->input('sort', '-created_at');
        match ($sort) {
            'created_at' => $query->orderBy('created_at', 'asc'),
            '-created_at' => $query->orderBy('created_at', 'desc'),
            'total_price' => $query->orderBy('total_price', 'asc'),
            '-total_price' => $query->orderBy('total_price', 'desc'),
            'expires_at' => $query->orderBy('expires_at', 'asc'),
            '-expires_at' => $query->orderBy('expires_at', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $bookings = $query->paginate(10)->withQueryString();

        return view('bookings.my-bookings', compact('bookings'));
    }

    /**
     * Cancel an active booking from web interface.
     */
    public function cancel(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $booking = Booking::with('court.venue')->findOrFail($id);

        try {
            $cancelledBooking = $this->bookingService->cancelBooking($booking, $user, $request->input('reason'));

            $msg = 'Booking berhasil dibatalkan.';
            if ($cancelledBooking->refund_amount > 0) {
                $msg .= ' Pengembalian DP sebesar Rp ' . number_format($cancelledBooking->refund_amount, 0, ',', '.') . ' telah disetujui.';
            }

            return back()->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
