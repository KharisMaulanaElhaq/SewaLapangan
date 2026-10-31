<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\ScheduleBlock;
use App\Models\User;
use App\Models\Venue;
use App\Models\WebhookLog;
use App\Services\BookingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Dashboard for Pengelola (Venue Owner).
     */
    public function pengelola(Request $request): View
    {
        $user = Auth::user();

        // Venues owned by this pengelola
        $venues = Venue::where('owner_id', $user->id)
            ->with(['courts.courtPrices', 'courts.operatingHours'])
            ->get();

        $courtIds = $venues->pluck('courts')->flatten()->pluck('id');

        // Metrics
        $totalRevenue = Payment::whereHas('booking', function ($q) use ($courtIds) {
            $q->whereIn('court_id', $courtIds);
        })->where('status', Payment::STATUS_SETTLEMENT)->sum('amount');

        $totalBookings = Booking::whereIn('court_id', $courtIds)->count();
        $confirmedBookings = Booking::whereIn('court_id', $courtIds)
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
            ->count();

        // Calculate simple occupancy percentage (confirmed slots vs total available slots this month)
        $occupancyRate = ($totalBookings > 0) ? round(($confirmedBookings / $totalBookings) * 100, 1) : 0;

        // Today's bookings
        $todayStart = Carbon::now('Asia/Jakarta')->startOfDay()->utc();
        $todayEnd = Carbon::now('Asia/Jakarta')->endOfDay()->utc();

        $todayBookings = Booking::with(['court.venue', 'user', 'bookingSlots'])
            ->whereIn('court_id', $courtIds)
            ->whereHas('bookingSlots', function ($q) use ($todayStart, $todayEnd) {
                $q->whereBetween('slot_start', [$todayStart, $todayEnd]);
            })
            ->latest()
            ->get();

        // All bookings with status filter
        $bookingsQuery = Booking::with(['court.venue', 'user', 'bookingSlots', 'payments'])
            ->whereIn('court_id', $courtIds)
            ->latest();

        if ($request->filled('status')) {
            $bookingsQuery->where('status', $request->status);
        }

        $bookings = $bookingsQuery->paginate(15);

        // Schedule blocks
        $scheduleBlocks = ScheduleBlock::whereIn('court_id', $courtIds)->with('court')->latest()->take(10)->get();

        return view('dashboard.pengelola', compact(
            'venues',
            'totalRevenue',
            'totalBookings',
            'confirmedBookings',
            'occupancyRate',
            'todayBookings',
            'bookings',
            'scheduleBlocks'
        ));
    }

    /**
     * Pengelola updates booking operational status (COMPLETED / NO_SHOW).
     */
    public function updateBookingStatus(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $booking = Booking::whereHas('court.venue', function ($q) use ($user) {
            if (!$user->isAdmin()) {
                $q->where('owner_id', $user->id);
            }
        })->findOrFail($id);

        $request->validate([
            'status' => ['required', 'string', 'in:COMPLETED,NO_SHOW'],
        ]);

        try {
            $this->bookingService->updateOperationalStatus($booking, $request->status);
            return back()->with('success', "Status booking {$booking->code} berhasil diubah ke {$request->status}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Quick store schedule block (maintenance / event).
     */
    public function storeBlock(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $request->validate([
            'court_id' => ['required', 'exists:courts,id'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $court = Court::whereHas('venue', function ($q) use ($user) {
            $q->where('owner_id', $user->id);
        })->findOrFail($request->court_id);

        ScheduleBlock::create([
            'court_id' => $court->id,
            'start_at' => Carbon::parse($request->start_at, 'Asia/Jakarta')->utc(),
            'end_at' => Carbon::parse($request->end_at, 'Asia/Jakarta')->utc(),
            'reason' => $request->reason,
        ]);

        return back()->with('success', 'Jadwal blokir / maintenance berhasil dibuat.');
    }

    /**
     * Store new court for a venue.
     */
    public function storeCourt(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $request->validate([
            'venue_id' => ['required', 'exists:venues,id'],
            'name' => ['required', 'string', 'max:255'],
            'sport_type' => ['required', 'string', 'in:futsal,badminton,basketball,tennis,mini_soccer,volleyball'],
            'price' => ['required', 'numeric', 'min:10000'],
            'open_time' => ['nullable', 'date_format:H:i'],
            'close_time' => ['nullable', 'date_format:H:i'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'photos.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $venue = Venue::where('owner_id', $user->id)->findOrFail($request->venue_id);

        $uploadedImages = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('courts', 'public');
                $uploadedImages[] = '/storage/' . $path;
            }
        } elseif ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('courts', 'public');
            $uploadedImages[] = '/storage/' . $path;
        }

        $mainImageUrl = !empty($uploadedImages) ? $uploadedImages[0] : $request->input('image_url');

        $court = Court::create([
            'venue_id' => $venue->id,
            'name' => $request->name,
            'sport_type' => $request->sport_type,
            'image_url' => $mainImageUrl,
            'images' => !empty($uploadedImages) ? $uploadedImages : ($mainImageUrl ? [$mainImageUrl] : null),
            'is_active' => true,
        ]);


        $openTime = $request->input('open_time', '08:00') . ':00';
        $closeTime = $request->input('close_time', '23:00') . ':00';

        // Create standard operating hours for all 7 days
        for ($day = 0; $day <= 6; $day++) {
            \App\Models\OperatingHour::create([
                'court_id' => $court->id,
                'day_of_week' => $day,
                'open_time' => $openTime,
                'close_time' => $closeTime,
                'slot_duration_minutes' => 60,
            ]);
        }

        // Create initial price tier
        \App\Models\CourtPrice::create([
            'court_id' => $court->id,
            'day_type' => 'all',
            'start_time' => $openTime,
            'end_time' => $closeTime,
            'price' => $request->price,
        ]);

        return back()->with('success', "Lapangan '{$court->name}' berhasil ditambahkan ke venue {$venue->name}.");
    }

    /**
     * Update existing court (name, sport_type, active status).
     */
    public function updateCourt(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $court = Court::whereHas('venue', function ($q) use ($user) {
            if (!$user->isAdmin()) {
                $q->where('owner_id', $user->id);
            }
        })->findOrFail($id);

        $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sport_type' => ['sometimes', 'required', 'string', 'in:futsal,badminton,basketball,tennis,mini_soccer,volleyball'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($request->has('toggle_active')) {
            $court->update(['is_active' => !$court->is_active]);
            $statusLabel = $court->is_active ? 'diaktifkan' : 'dinonaktifkan';
            return back()->with('success', "Status lapangan '{$court->name}' berhasil {$statusLabel}.");
        }

        $court->update($request->only(['name', 'sport_type', 'is_active']));

        return back()->with('success', "Data lapangan '{$court->name}' berhasil diperbarui.");
    }

    /**
     * Delete court.
     */
    public function destroyCourt(int $id): RedirectResponse
    {
        $user = Auth::user();
        $court = Court::whereHas('venue', function ($q) use ($user) {
            if (!$user->isAdmin()) {
                $q->where('owner_id', $user->id);
            }
        })->findOrFail($id);

        $courtName = $court->name;
        $court->delete();

        return back()->with('success', "Lapangan '{$courtName}' berhasil dihapus.");
    }

    /**
     * Store court pricing tier.
     */
    public function storePrice(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $request->validate([
            'court_id' => ['required', 'exists:courts,id'],
            'day_type' => ['required', 'string', 'in:weekday,weekend,all'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'price' => ['required', 'numeric', 'min:10000'],
        ]);

        $court = Court::whereHas('venue', function ($q) use ($user) {
            if (!$user->isAdmin()) {
                $q->where('owner_id', $user->id);
            }
        })->findOrFail($request->court_id);

        $startTime = strlen($request->start_time) === 5 ? $request->start_time . ':00' : $request->start_time;
        $endTime = strlen($request->end_time) === 5 ? $request->end_time . ':00' : $request->end_time;

        \App\Models\CourtPrice::create([
            'court_id' => $court->id,
            'day_type' => $request->day_type,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'price' => $request->price,
        ]);

        return back()->with('success', "Aturan harga baru untuk '{$court->name}' berhasil disimpan.");
    }

    /**
     * Delete court pricing tier.
     */
    public function destroyPrice(int $id): RedirectResponse
    {
        $user = Auth::user();
        $price = \App\Models\CourtPrice::whereHas('court.venue', function ($q) use ($user) {
            if (!$user->isAdmin()) {
                $q->where('owner_id', $user->id);
            }
        })->findOrFail($id);

        $price->delete();

        return back()->with('success', 'Aturan harga berhasil dihapus.');
    }

    /**
     * Dashboard for Platform Administrator.
     */
    public function admin(Request $request): View
    {
        // Overview metrics
        $totalUsers = User::count();
        $activePengelola = User::where('role', 'pengelola')->where('status', 'active')->count();
        $pendingPengelola = User::where('role', 'pengelola')->where('status', 'pending')->get();
        $totalRevenue = Payment::where('status', Payment::STATUS_SETTLEMENT)->sum('amount');
        $totalBookings = Booking::count();

        // Transactions list
        $transactionsQuery = Payment::with(['booking.user', 'booking.court.venue'])->latest();
        if ($request->filled('payment_status')) {
            $transactionsQuery->where('status', $request->payment_status);
        }
        $transactions = $transactionsQuery->paginate(10, ['*'], 'transactions_page');

        // Webhook logs
        $webhookLogs = WebhookLog::latest()->take(20)->get();
        $failedWebhooksCount = WebhookLog::where('signature_valid', false)
            ->orWhere('status', 'error')
            ->count();

        return view('dashboard.admin', compact(
            'totalUsers',
            'activePengelola',
            'pendingPengelola',
            'totalRevenue',
            'totalBookings',
            'transactions',
            'webhookLogs',
            'failedWebhooksCount'
        ));
    }

    /**
     * Admin verifies or rejects pengelola.
     */
    public function verifyPengelola(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'in:approve,reject'],
        ]);

        $pengelola = User::where('role', 'pengelola')->findOrFail($id);

        if ($request->action === 'approve') {
            $pengelola->update(['status' => 'active']);
            $msg = "Pengelola {$pengelola->name} ({$pengelola->email}) berhasil disetujui & diaktifkan.";
        } else {
            $pengelola->update(['status' => 'suspended']);
            $msg = "Pendaftaran pengelola {$pengelola->name} telah ditolak.";
        }

        return back()->with('success', $msg);
    }
}
