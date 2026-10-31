<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\UserResource;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\WebhookLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Allowed sorting parameters for GET /api/v1/admin/users (OpenAPI v1 whitelist).
     */
    private const ALLOWED_USER_SORTS = [
        'name',
        '-name',
        'email',
        'created_at',
        '-created_at',
    ];

    /**
     * List all users with optional filtering, search, and sorting.
     */
    public function users(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role' => ['nullable', 'string', 'in:penyewa,pengelola,admin'],
            'status' => ['nullable', 'string', 'in:active,pending,suspended'],
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'string', 'in:' . implode(',', self::ALLOWED_USER_SORTS)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter filter, pencarian, atau pengurutan pengguna tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $keyword = $request->input('q');
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                  ->orWhere('email', 'like', '%' . $keyword . '%')
                  ->orWhere('phone', 'like', '%' . $keyword . '%');
            });
        }

        $sort = $request->input('sort', '-created_at');
        match ($sort) {
            'name' => $query->orderBy('name', 'asc'),
            '-name' => $query->orderBy('name', 'desc'),
            'email' => $query->orderBy('email', 'asc'),
            'created_at' => $query->orderBy('created_at', 'asc'),
            '-created_at' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $perPage = min((int) $request->input('per_page', 20), 100);
        if ($perPage < 1) {
            $perPage = 20;
        }

        $users = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'status' => 'success',
            'data' => UserResource::collection($users),
            'links' => [
                'first' => $users->url(1),
                'last' => $users->url($users->lastPage()),
                'prev' => $users->previousPageUrl(),
                'next' => $users->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }

    /**
     * Verify / approve or reject pending pengelola.
     */
    public function verifyPengelola(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:active,suspended'],
        ]);

        $user = User::where('role', 'pengelola')->findOrFail($id);

        $user->update([
            'status' => $request->status,
        ]);

        $statusLabel = ($request->status === 'active') ? 'diverifikasi dan diaktifkan' : 'ditolak / dinonaktifkan';

        return response()->json([
            'status' => 'success',
            'message' => "Pengelola {$user->name} berhasil {$statusLabel}.",
            'data' => new UserResource($user),
        ]);
    }

    /**
     * View all transactions and revenue metrics.
     */
    public function transactions(Request $request): JsonResponse
    {
        $query = Payment::with(['booking.user', 'booking.court.venue'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->paginate($request->input('per_page', 20));

        $totalSettledRevenue = Payment::where('status', Payment::STATUS_SETTLEMENT)->sum('amount');
        $totalBookings = Booking::count();
        $totalConfirmedBookings = Booking::whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])->count();

        return response()->json([
            'status' => 'success',
            'data' => PaymentResource::collection($payments),
            'metrics' => [
                'total_settled_dp' => (float) $totalSettledRevenue,
                'total_bookings' => $totalBookings,
                'confirmed_bookings' => $totalConfirmedBookings,
            ],
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    /**
     * Monitor incoming and failed webhooks.
     */
    public function webhookLogs(Request $request): JsonResponse
    {
        $query = WebhookLog::latest();

        if ($request->boolean('failed_only')) {
            $query->where(function ($q) {
                $q->where('signature_valid', false)
                  ->orWhere('status', 'error')
                  ->orWhere('status', 'rejected');
            });
        }

        $logs = $query->paginate($request->input('per_page', 25));

        return response()->json([
            'status' => 'success',
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'total' => $logs->total(),
            ],
        ]);
    }
}
