<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelBookingRequest;
use App\Http\Requests\CreateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\PaymentResource;
use App\Models\Booking;
use App\Models\Court;
use App\Services\BookingService;
use App\Services\MidtransService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected MidtransService $midtransService
    ) {}

    /**
     * List bookings for current user / pengelola / admin.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Booking::with(['court.venue', 'bookingSlots', 'payments', 'user']);

        if ($user->isAdmin()) {
            // Admin sees all
        } elseif ($user->isPengelola()) {
            // Pengelola sees bookings for their venues
            $query->whereHas('court.venue', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            });
        } else {
            // Penyewa sees own bookings
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate($request->input('per_page', 10));

        return response()->json([
            'status' => 'success',
            'data' => BookingResource::collection($bookings),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    /**
     * Show booking details.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $booking = Booking::with(['court.venue', 'bookingSlots', 'payments', 'user'])->findOrFail($id);

        if ($booking->user_id !== $user->id && !$user->isAdmin() && $booking->court->venue->owner_id !== $user->id) {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'Anda tidak memiliki akses ke booking ini.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => new BookingResource($booking),
        ]);
    }

    /**
     * Create a new booking with automatic hold (15 minutes) and concurrency lock.
     */
    public function store(CreateBookingRequest $request): JsonResponse
    {
        $user = $request->user();
        $court = Court::with('venue')->findOrFail($request->court_id);

        try {
            $booking = $this->bookingService->createBooking(
                user: $user,
                court: $court,
                slotStartsUtc: $request->slots,
                notes: $request->notes
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Booking berhasil dibuat. Slot Anda telah di-HOLD selama 15 menit. Silakan segera selesaikan pembayaran DP.',
                'data' => new BookingResource($booking),
            ], 200);
        } catch (Exception $e) {
            $statusCode = str_contains($e->getMessage(), 'sedang dalam proses') || str_contains($e->getMessage(), 'sudah terisi') ? 409 : 422;

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Cancel an active booking.
     */
    public function cancel(CancelBookingRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $booking = Booking::with('court.venue')->findOrFail($id);

        try {
            $cancelledBooking = $this->bookingService->cancelBooking($booking, $user, $request->reason);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking berhasil dibatalkan.',
                'data' => new BookingResource($cancelledBooking),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Generate Midtrans Snap payment token and URL for DP payment.
     */
    public function pay(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $booking = Booking::with('court.venue')->findOrFail($id);

        if ($booking->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'Anda tidak memiliki hak akses untuk membayar booking ini.',
            ], 403);
        }

        try {
            $payment = $this->midtransService->createSnapTransaction($booking, 'DP');

            return response()->json([
                'status' => 'success',
                'message' => 'Token transaksi Midtrans Snap berhasil dibuat.',
                'data' => new PaymentResource($payment),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
