@extends('layouts.app')

@section('title', 'Pembayaran DP Booking ' . $booking->code)

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8 space-y-6 pb-20" x-data="checkoutManager()">
    <!-- Header Status Card -->
    <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-soft-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-6">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kode Booking</span>
                <h1 class="text-2xl sm:text-3xl font-black text-ink-900 font-mono mt-0.5 tracking-tight">{{ $booking->code }}</h1>
                <p class="text-xs text-slate-500 mt-1">Dibuat pada: {{ $booking->created_at->setTimezone('Asia/Jakarta')->isoFormat('D MMMM Y, HH:mm') }} WIB</p>
            </div>

            <!-- Status Badge with Icon + Text -->
            <div>
                @if($booking->isPendingPayment())
                    <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-4 py-2 text-xs sm:text-sm font-bold text-amber-800 border border-amber-300 shadow-soft-xs">
                        <span class="h-2 w-2 rounded-full bg-amber-500 animate-ping"></span>
                        Menunggu Pembayaran DP
                    </span>
                @elseif($booking->isConfirmed())
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-xs sm:text-sm font-bold text-emerald-800 border border-emerald-200 shadow-soft-xs">
                        <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Booking Terkonfirmasi
                    </span>
                @elseif($booking->isCompleted())
                    <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-xs sm:text-sm font-bold text-blue-800 border border-blue-200 shadow-soft-xs">
                        <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Selesai Digunakan
                    </span>
                @elseif($booking->isExpired())
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-xs sm:text-sm font-bold text-slate-600 border border-slate-200">
                        Kedaluwarsa (Expired)
                    </span>
                @elseif($booking->isCancelled())
                    <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-4 py-2 text-xs sm:text-sm font-bold text-rose-800 border border-rose-200">
                        Dibatalkan
                    </span>
                @endif
            </div>
        </div>

        <!-- 15-Minute Countdown Timer Banner (Prominent Luxury Gold/Amber Accent) -->
        @if($booking->isPendingPayment())
            <div class="mt-6 rounded-3xl bg-gradient-to-r from-amber-50 via-amber-100/40 to-orange-50 border-2 border-amber-300 p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-5 shadow-soft-md">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-500/25">
                        <svg class="h-6 w-6 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-amber-500/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-amber-900 border border-amber-400/40">
                                HOLD SLOT REAL-TIME
                            </span>
                        </div>
                        <h4 class="text-sm sm:text-base font-black text-amber-950 mt-1">Slot Lapangan Sedang Di-HOLD!</h4>
                        <p class="text-xs text-amber-900 leading-relaxed mt-0.5">Selesaikan pembayaran DP sebelum batas waktu habis agar slot tidak dirilis kembali ke pemesan lain.</p>
                    </div>
                </div>

                <div class="text-left sm:text-right rounded-2xl bg-white/70 backdrop-blur-sm px-4 py-2.5 border border-amber-200 self-start sm:self-auto min-w-[130px]">
                    <span class="block text-[10px] text-amber-900 font-bold uppercase tracking-wider">Sisa Waktu</span>
                    <span class="text-2xl sm:text-3xl font-black font-mono text-amber-950 tracking-wider" x-text="countdownFormatted">--:--</span>
                </div>
            </div>
        @endif

        <!-- Booking Details & Breakdown -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Venue & Slot Information -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Informasi Lapangan & Jadwal</h3>
                
                <div class="rounded-3xl border border-slate-200/90 bg-slate-50/70 p-5 space-y-3.5 text-xs">
                    <div>
                        <span class="text-slate-400 block font-medium">Nama Venue</span>
                        <span class="font-black text-ink-900 text-base leading-tight">{{ $booking->court->venue->name }}</span>
                        <span class="text-slate-500 block text-xs mt-0.5">{{ $booking->court->venue->address }}</span>
                    </div>

                    <div class="border-t border-slate-200/80 pt-3 flex justify-between items-center">
                        <div>
                            <span class="text-slate-400 block font-medium">Unit Lapangan</span>
                            <span class="font-bold text-emerald-800 text-sm">{{ $booking->court->name }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-slate-400 block font-medium">Olahraga</span>
                            <span class="rounded-lg bg-emerald-50 px-2 py-0.5 font-bold text-emerald-800 uppercase font-mono text-[11px] border border-emerald-200">
                                {{ $booking->court->sport_type }}
                            </span>
                        </div>
                    </div>

                    <div class="border-t border-slate-200/80 pt-3">
                        <span class="text-slate-400 block mb-2 font-medium">Daftar Slot Waktu Dipesan:</span>
                        <div class="space-y-1.5">
                            @foreach($booking->bookingSlots as $slot)
                                <div class="flex justify-between items-center bg-white px-3.5 py-2 rounded-xl border border-slate-200 font-mono text-xs shadow-soft-xs">
                                    <span class="font-bold text-ink-900">
                                        {{ \Carbon\Carbon::parse($slot->slot_start)->setTimezone('Asia/Jakarta')->isoFormat('D MMM Y') }}
                                        · {{ \Carbon\Carbon::parse($slot->slot_start)->setTimezone('Asia/Jakarta')->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->slot_end)->setTimezone('Asia/Jakarta')->format('H:i') }}
                                    </span>
                                    <span class="text-emerald-700 font-black">Rp {{ number_format($slot->price, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($booking->notes)
                        <div class="border-t border-slate-200/80 pt-3">
                            <span class="text-slate-400 block font-medium">Catatan Pemesan</span>
                            <span class="text-slate-700 italic">{{ $booking->notes }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment Summary & Actions -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rincian Pembayaran</h3>

                <div class="rounded-3xl border border-slate-200/90 bg-slate-50/70 p-5 space-y-3.5 text-xs">
                    <div class="flex justify-between items-center text-slate-600">
                        <span class="font-medium">Total Biaya Sewa ({{ $booking->bookingSlots->count() }} Slot)</span>
                        <span class="font-mono font-black text-ink-900 text-sm">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-center text-emerald-900 font-bold border-t border-slate-200/80 pt-3">
                        <div>
                            <span class="block">DP Di Muka Wajib Dibayar</span>
                            <span class="text-[10px] text-emerald-600 font-normal">({{ $booking->court->venue->dp_percent }}% dari total)</span>
                        </div>
                        <span class="font-mono text-xl text-emerald-600 font-black">Rp {{ number_format($booking->dp_amount, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-center text-slate-400 text-xs">
                        <span>Sisa Pelunasan di Lokasi</span>
                        <span class="font-mono font-semibold text-slate-600">Rp {{ number_format($booking->total_price - $booking->dp_amount, 0, ',', '.') }}</span>
                    </div>

                    @if($booking->refund_amount > 0)
                        <div class="flex justify-between items-center text-amber-900 bg-amber-100/70 p-3 rounded-2xl font-bold border border-amber-200">
                            <span>Dana Dikembalikan (Refund)</span>
                            <span class="font-mono text-sm">Rp {{ number_format($booking->refund_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Action Buttons according to status -->
                @if($booking->isPendingPayment() && $payment)
                    <div class="space-y-3 pt-2">
                        <!-- Midtrans Snap Popup Button -->
                        <button type="button" @click="payWithSnap()"
                            class="w-full rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 py-3.5 px-6 font-black text-white text-sm sm:text-base shadow-xl shadow-emerald-600/30 hover:from-emerald-700 hover:to-teal-700 active:scale-[0.98] transition flex items-center justify-center gap-2.5 min-h-[48px]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Bayar DP via Midtrans Snap (Rp {{ number_format($booking->dp_amount, 0, ',', '.') }})</span>
                        </button>

                        <!-- Sandbox Simulator Button (PRD Requirement Testing Support) -->
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/90 p-4 text-center space-y-2">
                            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Simulasi Pengujian Sandbox Midtrans:</span>
                            <button type="button" @click="simulateSandboxSettlement()"
                                class="w-full rounded-xl bg-ink-900 py-2.5 px-4 font-bold text-white text-xs hover:bg-slate-800 transition flex items-center justify-center gap-2 min-h-[44px]">
                                <svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span>Simulasi Pembayaran Sukses (Webhook Instant)</span>
                            </button>
                            <p class="text-[10px] text-slate-400">Mengirim simulasi webhook resmi Midtrans dengan validasi HMAC SHA-512 signature.</p>
                        </div>
                    </div>
                @elseif($booking->isConfirmed())
                    <div class="rounded-3xl border border-emerald-200 bg-emerald-50/70 p-6 text-center space-y-3.5 shadow-soft-sm">
                        <div class="flex justify-center">
                            <div class="h-12 w-12 rounded-2xl bg-emerald-600 flex items-center justify-center text-white shadow-md shadow-emerald-600/30">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                        <h4 class="font-black text-ink-900 text-lg">Pembayaran DP Berhasil Diterima</h4>
                        <p class="text-xs text-emerald-900 max-w-sm mx-auto leading-relaxed">
                            Slot lapangan Anda telah resmi terkonfirmasi. Tunjukkan kode booking <strong class="font-mono text-sm font-black">{{ $booking->code }}</strong> kepada pengelola venue saat hadir di lokasi.
                        </p>
                        <div class="pt-2 flex flex-wrap justify-center gap-2.5">
                            <a href="{{ \Illuminate\Support\Facades\Route::has('booking.invoice') ? route('booking.invoice', $booking->id) : route('booking.checkout', ['id' => $booking->id, 'view' => 'invoice']) }}"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-ink-900 px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-800 transition min-h-[44px] shadow-soft-xs">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                <span>Cetak E-Ticket / Invoice</span>
                            </a>
                            <a href="{{ route('my.bookings') }}" class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-emerald-700 transition min-h-[44px]">
                                Lihat Booking Saya
                            </a>
                            <a href="{{ route('home') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition min-h-[44px]">
                                Pesan Lapangan Lain
                            </a>
                        </div>
                    </div>
                @elseif($booking->isExpired())
                    <div class="rounded-3xl border border-slate-200 bg-slate-50/90 p-6 text-center space-y-3">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-slate-200 text-slate-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-xs text-slate-500">Batas waktu pembayaran DP telah habis. Slot telah dilepaskan kembali ke publik.</p>
                        <a href="{{ route('venues.show', $booking->court->venue_id) }}" class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition min-h-[44px]">
                            Pilih Jadwal Ulang
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function checkoutManager() {
        return {
            expiresAt: new Date("{{ $booking->expires_at?->toIso8601String() }}").getTime(),
            countdownFormatted: '--:--',
            snapToken: "{{ $payment->snap_token ?? '' }}",
            orderId: "{{ $payment->order_id ?? '' }}",

            init() {
                @if($booking->isPendingPayment())
                    this.updateCountdown();
                    setInterval(() => this.updateCountdown(), 1000);
                @endif
            },

            updateCountdown() {
                const now = new Date().getTime();
                const diff = this.expiresAt - now;

                if (diff <= 0) {
                    this.countdownFormatted = '00:00';
                    window.location.reload();
                    return;
                }

                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                this.countdownFormatted = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            },

            payWithSnap() {
                if (!this.snapToken) {
                    alert('Token Midtrans Snap tidak tersedia.');
                    return;
                }

                if (typeof window.snap === 'undefined') {
                    alert('Midtrans Snap SDK gagal dimuat. Gunakan simulasi sandbox di bawah.');
                    return;
                }

                window.snap.pay(this.snapToken, {
                    onSuccess: (result) => {
                        this.simulateSandboxSettlement();
                    },
                    onPending: (result) => {
                        alert('Menunggu penyelesaian pembayaran di gateway.');
                    },
                    onError: (result) => {
                        alert('Pembayaran gagal atau dibatalkan.');
                    },
                    onClose: () => {
                        console.log('Customer closed payment popup.');
                    }
                });
            },

            async simulateSandboxSettlement() {
                try {
                    const res = await fetch("{{ url('/api/v1/payments/simulate') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            order_id: this.orderId,
                            transaction_status: "settlement",
                            payment_type: "qris"
                        })
                    });

                    const data = await res.json();
                    if (data.status === 'success') {
                        alert('Simulasi pembayaran sandbox berhasil! Webhook telah diproses.');
                        window.location.reload();
                    } else {
                        alert('Gagal simulasi: ' + data.message);
                    }
                } catch (e) {
                    alert('Terjadi kesalahan: ' + e.message);
                }
            }
        }
    }
</script>
@endpush
@endsection
