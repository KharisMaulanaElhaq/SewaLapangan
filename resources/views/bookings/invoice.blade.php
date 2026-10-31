@extends('layouts.app')

@section('title', 'E-Ticket & Invoice ' . $booking->code)

@push('styles')
<style>
    @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        header, footer, .no-print {
            display: none !important;
        }
        body {
            background: #ffffff !important;
        }
        .print-container {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
        .print-card {
            box-shadow: none !important;
            border: 1px solid #cbd5e1 !important;
        }
        .print-header {
            background: #0f172a !important;
            color: #ffffff !important;
            border-bottom: 2px solid #059669 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="print-container mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8 space-y-6">
    <!-- Top Action Toolbar (Hidden on Print) -->
    <div class="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('my.bookings') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Riwayat Booking
            </a>
            <a href="{{ route('booking.checkout', $booking->id) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                Detail Pembayaran
            </a>
        </div>

        <div class="flex items-center gap-2.5">
            <button type="button" onclick="window.print()"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-600/20 hover:bg-emerald-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak E-Ticket / Invoice (PDF)
            </button>
        </div>
    </div>

    <!-- Official Printable E-Ticket & Invoice Card -->
    <div class="print-card overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-lg">
        <!-- Ticket Header Banner -->
        <div class="print-header bg-gradient-to-r from-slate-900 via-emerald-950 to-slate-900 px-6 py-7 sm:px-10 text-white">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <!-- Self-contained SVG Logo so it always renders in Print/PDF even without CSS background graphics -->
                    <div class="flex-shrink-0">
                        <svg class="h-14 w-14 rounded-2xl shadow-lg" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="56" height="56" rx="14" fill="#059669"/>
                            <circle cx="28" cy="28" r="14" stroke="#FFFFFF" stroke-width="3"/>
                            <path d="M28 14V42M14 28H42" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-extrabold tracking-tight text-emerald-400">SportVenue</span>
                            <span class="text-slate-500">•</span>
                            <span class="text-[11px] font-bold uppercase tracking-widest text-emerald-300">Official E-Ticket</span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white mt-0.5">E-TICKET & BUKTI BOOKING LAPANGAN</h1>
                        <p class="text-xs text-slate-300 mt-0.5">Diterbitkan pada: {{ $booking->created_at?->setTimezone('Asia/Jakarta')->isoFormat('D MMMM Y, H:mm') }} WIB</p>
                    </div>
                </div>

                <!-- Payment / Booking Official Status Stamp -->
                <div class="sm:text-right">
                    <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 block mb-1">Status Verifikasi</span>
                    @if($booking->isConfirmed() || $booking->isCompleted())
                        <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-500/20 border border-emerald-400/50 px-4 py-2 text-xs font-extrabold uppercase tracking-wider text-emerald-300">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            {{ $booking->isCompleted() ? 'SELESAI DIGUNAKAN (PAID)' : 'LUNAS DP · TERKONFIRMASI' }}
                        </span>
                    @elseif($booking->isPendingPayment())
                        <span class="inline-flex items-center gap-2 rounded-xl bg-amber-500/20 border border-amber-400/50 px-4 py-2 text-xs font-extrabold uppercase tracking-wider text-amber-300">
                            <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                            MENUNGGU PEMBAYARAN DP
                        </span>
                    @elseif($booking->isCancelled())
                        <span class="inline-flex items-center gap-2 rounded-xl bg-rose-500/20 border border-rose-400/50 px-4 py-2 text-xs font-extrabold uppercase tracking-wider text-rose-300">
                            DIBATALKAN (CANCELLED)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-xl bg-slate-700 border border-slate-600 px-4 py-2 text-xs font-extrabold uppercase tracking-wider text-slate-300">
                            {{ $booking->status }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Booking Code + QR Verification Strip -->
        <div class="border-b border-dashed border-slate-200 bg-slate-50/70 px-6 py-6 sm:px-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-1.5">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Kode Booking Resmi (Booking Reference)</span>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="font-mono text-2xl sm:text-3xl font-black tracking-wider text-slate-900">{{ $booking->code }}</span>
                        @if($payment && $payment->status === \App\Models\Payment::STATUS_SETTLEMENT)
                            <span class="rounded-lg bg-emerald-100 px-3 py-1 text-xs font-extrabold text-emerald-800 border border-emerald-300">
                                ✓ DP LUNAS ({{ strtoupper($payment->payment_type ?: 'MIDTRANS') }})
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500">
                        Tunjukkan kode booking atau pindai QR E-Ticket ini kepada petugas pengelola di lokasi venue saat check-in.
                    </p>
                </div>

                <!-- QR Code Verification Box -->
                <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm self-start md:self-auto">
                    <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-1.5" title="QR Code {{ $booking->code }}">
                        <!-- Deterministic SVG QR Matrix Representation for Offline/Print Reliability -->
                        <svg viewBox="0 0 100 100" class="h-full w-full text-slate-900" fill="currentColor">
                            <!-- Finder Pattern Top-Left -->
                            <path d="M5,5 h28 v28 h-28 z M10,10 v18 h18 v-18 z M14,14 h10 v10 h-10 z"/>
                            <!-- Finder Pattern Top-Right -->
                            <path d="M67,5 h28 v28 h-28 z M72,10 v18 h18 v-18 z M76,14 h10 v10 h-10 z"/>
                            <!-- Finder Pattern Bottom-Left -->
                            <path d="M5,67 h28 v28 h-28 z M10,72 v18 h18 v-18 z M14,76 h10 v10 h-10 z"/>
                            <!-- Data Modules -->
                            <rect x="39" y="7" width="6" height="6"/>
                            <rect x="51" y="7" width="6" height="6"/>
                            <rect x="45" y="13" width="6" height="12"/>
                            <rect x="39" y="25" width="6" height="6"/>
                            <rect x="57" y="19" width="6" height="6"/>
                            <rect x="7" y="39" width="6" height="6"/>
                            <rect x="19" y="39" width="12" height="6"/>
                            <rect x="39" y="39" width="12" height="6"/>
                            <rect x="57" y="39" width="6" height="12"/>
                            <rect x="75" y="39" width="12" height="6"/>
                            <rect x="13" y="51" width="6" height="6"/>
                            <rect x="27" y="47" width="6" height="12"/>
                            <rect x="45" y="47" width="6" height="6"/>
                            <rect x="39" y="57" width="12" height="6"/>
                            <rect x="67" y="51" width="6" height="6"/>
                            <rect x="81" y="51" width="12" height="6"/>
                            <rect x="39" y="69" width="6" height="12"/>
                            <rect x="51" y="67" width="12" height="6"/>
                            <rect x="69" y="67" width="6" height="6"/>
                            <rect x="81" y="69" width="6" height="12"/>
                            <rect x="47" y="83" width="6" height="10"/>
                            <rect x="59" y="79" width="12" height="6"/>
                            <rect x="75" y="87" width="18" height="6"/>
                        </svg>
                    </div>
                    <div class="text-xs space-y-1 pr-1">
                        <span class="inline-block rounded bg-slate-900 px-2 py-0.5 font-mono text-[10px] font-bold text-white">E-TICKET QR</span>
                        <p class="font-mono font-bold text-slate-800 text-xs">{{ $booking->code }}</p>
                        <p class="text-[11px] text-slate-500">ID Transaksi: #{{ $booking->id }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Body Content -->
        <div class="p-6 sm:p-10 space-y-8">
            <!-- Customer & Venue Information Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Penyewa Info -->
                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/50 p-5 space-y-3">
                    <div class="flex items-center gap-2 border-b border-slate-200/70 pb-2.5">
                        <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700">Data Penyewa (Customer)</h2>
                    </div>
                    <dl class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Nama Lengkap</dt>
                            <dd class="font-bold text-slate-900">{{ $booking->user->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Email</dt>
                            <dd class="font-mono text-slate-800">{{ $booking->user->email }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">No. Telepon / WhatsApp</dt>
                            <dd class="font-mono font-semibold text-slate-800">{{ $booking->user->phone ?: '-' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Venue & Court Info -->
                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/50 p-5 space-y-3">
                    <div class="flex items-center gap-2 border-b border-slate-200/70 pb-2.5">
                        <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700">Lokasi Venue & Lapangan</h2>
                    </div>
                    <dl class="space-y-2 text-xs">
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Nama Venue</dt>
                            <dd class="font-extrabold text-slate-900 text-right">{{ $booking->court->venue->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Unit Lapangan</dt>
                            <dd class="font-bold text-emerald-700 text-right">
                                {{ $booking->court->name }}
                                <span class="ml-1 rounded bg-emerald-100 px-1.5 py-0.5 font-mono text-[10px] uppercase text-emerald-800">{{ $booking->court->sport_type }}</span>
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Alamat Venue</dt>
                            <dd class="text-slate-700 text-right">{{ $booking->court->venue->address }}, {{ $booking->court->venue->city }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Kontak Venue</dt>
                            <dd class="font-mono text-slate-800 text-right">{{ $booking->court->venue->phone }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Rincian Jam Main (Booked Time Slots Table) -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700">Rincian Jadwal & Jam Main</h2>
                    <span class="text-xs font-semibold text-slate-500">Total: {{ $booking->bookingSlots->count() }} Slot Jam</span>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left font-bold uppercase tracking-wider">No</th>
                                <th scope="col" class="px-4 py-3 text-left font-bold uppercase tracking-wider">Lapangan</th>
                                <th scope="col" class="px-4 py-3 text-left font-bold uppercase tracking-wider">Tanggal Main</th>
                                <th scope="col" class="px-4 py-3 text-left font-bold uppercase tracking-wider">Jam Sesi (WIB)</th>
                                <th scope="col" class="px-4 py-3 text-right font-bold uppercase tracking-wider">Tarif Slot</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($booking->bookingSlots as $index => $slot)
                                <tr>
                                    <td class="px-4 py-3.5 font-mono text-slate-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3.5 font-semibold text-slate-900">{{ $booking->court->name }}</td>
                                    <td class="px-4 py-3.5 font-medium text-slate-800">
                                        {{ \Carbon\Carbon::parse($slot->slot_start)->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y') }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center rounded-lg bg-emerald-50 px-2.5 py-1 font-mono font-bold text-emerald-800 border border-emerald-200">
                                            {{ \Carbon\Carbon::parse($slot->slot_start)->setTimezone('Asia/Jakarta')->format('H:i') }}
                                            &ndash;
                                            {{ \Carbon\Carbon::parse($slot->slot_end)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900">
                                        Rp {{ number_format($slot->price, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-slate-400 italic">
                                        Slot jadwal telah dilepas karena pesanan berstatus {{ $booking->status }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Financial Summary & Payment Audit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <!-- Left: Payment Gateway Info & Policy -->
                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/50 p-4 text-xs space-y-2">
                        <span class="font-bold text-slate-700 uppercase tracking-wider block">Informasi Pembayaran Digital</span>
                        @if($payment)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Order ID Midtrans</span>
                                <span class="font-mono font-semibold text-slate-800">{{ $payment->order_id }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Metode Pembayaran</span>
                                <span class="font-semibold uppercase text-slate-800">{{ $payment->payment_type ?: 'Midtrans Snap' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Status Transaksi</span>
                                <span class="font-mono font-bold {{ $payment->status === 'settlement' ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ strtoupper($payment->status) }}
                                </span>
                            </div>
                            @if($payment->paid_at)
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Waktu Lunas (Paid At)</span>
                                    <span class="font-mono text-slate-800">{{ $payment->paid_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</span>
                                </div>
                            @endif
                        @else
                            <p class="text-slate-500 italic">Belum ada catatan transaksi pembayaran digital.</p>
                        @endif
                    </div>

                    @if($booking->notes)
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 text-xs space-y-1">
                            <span class="font-bold text-slate-700 block">Catatan Penyewa:</span>
                            <p class="text-slate-600 italic">"{{ $booking->notes }}"</p>
                        </div>
                    @endif
                </div>

                <!-- Right: Invoice Calculation Box -->
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 space-y-3 text-xs">
                    <h3 class="font-extrabold uppercase tracking-wider text-slate-700 border-b border-slate-200 pb-2">Ringkasan Tagihan (Invoice)</h3>

                    <div class="flex justify-between text-slate-600">
                        <span>Total Harga Sewa Lapangan</span>
                        <span class="font-mono font-bold text-slate-900">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-center text-emerald-800 font-bold border-t border-slate-200/80 pt-2.5">
                        <span>Tagihan Uang Muka / DP ({{ $booking->court->venue->dp_percent }}%)</span>
                        <span class="font-mono text-base font-black text-emerald-600">Rp {{ number_format($booking->dp_amount, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-slate-600 border-t border-slate-200/80 pt-2.5">
                        <span>Sisa Pelunasan di Lokasi Venue</span>
                        <span class="font-mono font-bold text-slate-900">Rp {{ number_format(max(0, $booking->total_price - $booking->dp_amount), 0, ',', '.') }}</span>
                    </div>

                    @if($booking->refund_amount > 0)
                        <div class="flex justify-between text-amber-800 bg-amber-100/70 p-2.5 rounded-xl font-bold">
                            <span>Nominal Pengembalian Dana (Refund)</span>
                            <span class="font-mono">Rp {{ number_format($booking->refund_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer Terms & Check-in Instructions -->
            <div class="border-t border-slate-200 pt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-[11px] text-slate-500">
                <div class="space-y-1">
                    <p class="font-bold text-slate-700">Ketentuan Check-in & Kebijakan Pembatalan Venue:</p>
                    <p>1. Harap hadir di lokasi minimal 15 menit sebelum jam bermain dimulai dengan menunjukkan E-Ticket ini.</p>
                    <p>2. Pembatalan minimal {{ $booking->cancel_free_hours_snapshot }} jam sebelum jadwal berhak atas pengembalian DP sebesar {{ $booking->cancel_refund_percent_snapshot }}%.</p>
                </div>
                <div class="sm:text-right font-mono text-[10px] text-slate-400">
                    <p>Dicetak dari Sistem SportVenue</p>
                    <p>{{ now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

