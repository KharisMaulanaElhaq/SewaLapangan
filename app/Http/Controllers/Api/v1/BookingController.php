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
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Allowed sorting parameters for GET /api/v1/bookings (OpenAPI v1 whitelist).
     */
    private const ALLOWED_SORTS = [
        'created_at',
        '-created_at',
        'total_price',
        '-total_price',
        'expires_at',
        '-expires_at',
    ];

    /**
     * Allowed booking status values (OpenAPI v1 enum).
     */
    private const ALLOWED_STATUSES = [
        Booking::STATUS_PENDING_PAYMENT,
        Booking::STATUS_CONFIRMED,
        Booking::STATUS_COMPLETED,
        Booking::STATUS_CANCELLED,
        Booking::STATUS_EXPIRED,
        Booking::STATUS_NO_SHOW,
    ];

    public function __construct(
        protected BookingService $bookingService,
        protected MidtransService $midtransService
    ) {}

    /**
     * List bookings for current user / pengelola / admin (GET /api/v1/bookings).
     * Supports filtering by status, court_id, date_from, date_to, search code (q),
     * whitelist sorting (created_at, total_price, expires_at), and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => ['nullable', 'string', 'in:' . implode(',', self::ALLOWED_STATUSES)],
            'court_id' => ['nullable', 'integer', 'min:1'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => array_filter([
                'nullable',
                'date_format:Y-m-d',
                $request->filled('date_from') ? 'after_or_equal:date_from' : null,
            ]),
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'string', 'in:' . implode(',', self::ALLOWED_SORTS)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter filter atau pengurutan riwayat booking tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $query = Booking::with(['court.venue', 'bookingSlots', 'payments', 'user']);

        if ($user->isAdmin()) {
            // Admin sees all bookings
        } elseif ($user->isPengelola()) {
            // Pengelola sees bookings for their venues
            $query->whereHas('court.venue', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            });
        } else {
            // Penyewa sees own bookings
            $query->where('user_id', $user->id);
        }

        // Filter by booking status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by specific court_id
        if ($request->filled('court_id')) {
            $query->where('court_id', (int) $request->court_id);
        }

        // Filter by date_from (evaluated via child booking_slots.slot_start, with created_at fallback if slots were released)
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

        // Filter by date_to (evaluated via child booking_slots.slot_start, with created_at fallback if slots were released)
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

        // Search by unique booking code (q)
        if ($request->filled('q')) {
            $query->where('code', 'like', '%' . trim($request->q) . '%');
        }

        // Whitelist sorting (default: -created_at per OpenAPI v1)
        $sort = $request->input('sort', '-created_at');
        match ($sort) {
            'created_at' => $query->orderBy('bookings.created_at', 'asc'),
            '-created_at' => $query->orderBy('bookings.created_at', 'desc'),
            'total_price' => $query->orderBy('bookings.total_price', 'asc'),
            '-total_price' => $query->orderBy('bookings.total_price', 'desc'),
            'expires_at' => $query->orderBy('bookings.expires_at', 'asc'),
            '-expires_at' => $query->orderBy('bookings.expires_at', 'desc'),
            default => $query->orderBy('bookings.created_at', 'desc'),
        };

        $perPage = (int) $request->input('per_page', 15);
        $bookings = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'status' => 'success',
            'data' => BookingResource::collection($bookings),
            'links' => [
                'first' => $bookings->url(1),
                'last' => $bookings->url($bookings->lastPage()),
                'prev' => $bookings->previousPageUrl(),
                'next' => $bookings->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'per_page' => $bookings->perPage(),
                'from' => $bookings->firstItem(),
                'to' => $bookings->lastItem(),
                'total' => $bookings->total(),
                'last_page' => $bookings->lastPage(),
            ],
        ]);
    }

    /**
     * Show booking details (GET /api/v1/bookings/{id}).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $booking = Booking::with(['court.venue', 'bookingSlots', 'payments', 'user'])->find($id);

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi booking tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        if ($booking->user_id !== $user->id && !$user->isAdmin() && $booking->court->venue->owner_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses ke booking ini.',
                'code' => 'FORBIDDEN_ACCESS',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => new BookingResource($booking),
        ]);
    }

    /**
     * Create a new booking with automatic hold (15 minutes) and concurrency lock (POST /api/v1/bookings).
     * Returns 201 Created with Location header per OpenAPI v1.
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
            ], 201)->header('Location', '/api/v1/bookings/' . $booking->id);
        } catch (Exception $e) {
            $isConflict = str_contains($e->getMessage(), 'sedang dalam proses')
                || str_contains($e->getMessage(), 'sudah terisi')
                || str_contains($e->getMessage(), 'berbenturan');

            $statusCode = $isConflict ? 409 : 422;

            $payload = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];

            if ($statusCode === 409) {
                $payload['code'] = 'SLOT_ALREADY_LOCKED';
            }

            return response()->json($payload, $statusCode);
        }
    }

    /**
     * Cancel an active booking (POST /api/v1/bookings/{id}/cancellation or /cancel).
     */
    public function cancel(CancelBookingRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $booking = Booking::with('court.venue')->find($id);

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi booking tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        if ($booking->user_id !== $user->id && !$user->isAdmin() && $booking->court->venue->owner_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk membatalkan booking ini.',
                'code' => 'FORBIDDEN_ACCESS',
            ], 403);
        }

        try {
            $cancelledBooking = $this->bookingService->cancelBooking($booking, $user, $request->reason);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking berhasil dibatalkan.',
                'data' => new BookingResource($cancelledBooking),
            ]);
        } catch (Exception $e) {
            $isNotPermitted = str_contains($e->getMessage(), 'tidak dapat dibatalkan');
            $statusCode = $isNotPermitted ? 409 : 422;

            $payload = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];

            if ($statusCode === 409) {
                $payload['code'] = 'CANCEL_NOT_PERMITTED';
            }

            return response()->json($payload, $statusCode);
        }
    }

    /**
     * Generate Midtrans Snap payment token and URL for DP payment (POST /api/v1/bookings/{id}/payments or /pay).
     */
    public function pay(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $booking = Booking::with('court.venue')->find($id);

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi booking tidak ditemukan.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        if ($booking->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk membayar booking ini.',
                'code' => 'FORBIDDEN_ACCESS',
            ], 403);
        }

        try {
            $payment = $this->midtransService->createSnapTransaction($booking, 'DP');

            return response()->json([
                'status' => 'success',
                'message' => 'Token transaksi Midtrans Snap berhasil dibuat.',
                'snap_token' => $payment->snap_token,
                'redirect_url' => $payment->snap_redirect_url,
                'order_id' => $payment->order_id,
                'amount' => (float) $payment->amount,
                'data' => new PaymentResource($payment),
            ], 201);
        } catch (\App\Exceptions\PaymentNotAllowedException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'Transaksi sudah dibayar atau telah melewati batas holding pembayaran.',
                'code' => 'PAYMENT_NOT_ALLOWED',
            ], 409);
        } catch (Exception $e) {
            $isStateConflict = str_contains($e->getMessage(), 'PENDING_PAYMENT')
                || str_contains($e->getMessage(), 'kedaluwarsa')
                || str_contains($e->getMessage(), 'expired');

            $statusCode = $isStateConflict ? 409 : 422;

            $payload = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];

            if ($statusCode === 409) {
                $payload['code'] = 'PAYMENT_NOT_ALLOWED';
            }

            return response()->json($payload, $statusCode);
        }
    }
}
