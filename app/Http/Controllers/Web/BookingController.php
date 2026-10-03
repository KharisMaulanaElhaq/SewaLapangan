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
    public function checkout(int $id): View|RedirectResponse
    {
        $user = Auth::user();
        $booking = Booking::with(['court.venue', 'bookingSlots', 'payments'])->findOrFail($id);

        if ($booking->user_id !== $user->id && !$user->isAdmin()) {
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
     * Customer list of my bookings.
     */
    public function myBookings(Request $request): View
    {
        $user = Auth::user();
        $query = Booking::with(['court.venue', 'bookingSlots', 'payments'])
            ->where('user_id', $user->id)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate(10);

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
