<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourtController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Get real-time slot availability for a court on a date.
     */
    public function availability(Request $request, int $id): JsonResponse
    {
        $court = Court::where('is_active', true)->findOrFail($id);

        $dateString = $request->input('date', Carbon::now('Asia/Jakarta')->toDateString());

        // Basic date format validation
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format tanggal harus YYYY-MM-DD (contoh: 2026-10-04).',
            ], 422);
        }

        $availability = $this->bookingService->getAvailability($court, $dateString);

        return response()->json([
            'status' => 'success',
            'data' => $availability,
        ]);
    }
}
