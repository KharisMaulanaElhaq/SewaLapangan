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

class AdminController extends Controller
{
    /**
     * List all users with optional filtering.
     */
    public function users(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('email', 'like', '%' . $request->q . '%')
                  ->orWhere('phone', 'like', '%' . $request->q . '%');
            });
        }

        $users = $query->latest()->paginate($request->input('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
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
