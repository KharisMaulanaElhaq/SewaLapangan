@extends('layouts.app')

@section('title', 'Pembayaran DP Booking ' . $booking->code)

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8 space-y-6" x-data="checkoutManager()">
    <!-- Header Status Card -->
    <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-6">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kode Booking</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono mt-0.5">{{ $booking->code }}</h1>
                <p class="text-xs text-slate-500 mt-1">Dibuat pada: {{ $booking->created_at->setTimezone('Asia/Jakarta')->isoFormat('D MMMM Y, H:mm') }} WIB</p>
            </div>

            <!-- Status Badge -->
            <div>
                @if($booking->isPendingPayment())
                    <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-4 py-2 text-sm font-bold text-amber-700 border border-amber-200">
                        <span class="h-2 w-2 rounded-full bg-amber-500 animate-ping"></span>
                        Menunggu Pembayaran DP
                    </span>
                @elseif($booking->isConfirmed())
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700 border border-emerald-200">
                        <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Booking Terkonfirmasi
                    </span>
                @elseif($booking->isCompleted())
                    <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-sm font-bold text-blue-700 border border-blue-200">
                        Selesai Digunakan
                    </span>
                @elseif($booking->isExpired())
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm font-bold text-slate-500 border border-slate-200">
                        Kedaluwarsa (Expired)
                    </span>
                @elseif($booking->isCancelled())
                    <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-4 py-2 text-sm font-bold text-rose-700 border border-rose-200">
                        Dibatalkan
                    </span>
                @endif
            </div>
        </div>

        <!-- 15-Minute Countdown Timer Banner (Only if Pending) -->
        @if($booking->isPendingPayment())
            <div class="mt-6 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/80 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-white shadow-md shadow-amber-500/20">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-amber-950">Slot Anda Di-HOLD Sementara!</h4>
                        <p class="text-xs text-amber-800">Selesaikan pembayaran DP sebelum batas waktu berakhir agar slot tidak dilepas.</p>
                    </div>
                </div>

                <div class="text-right self-end sm:self-auto">
                    <span class="block text-[10px] text-amber-800 font-bold uppercase tracking-wider">Sisa Waktu Pembayaran</span>
                    <span class="text-2xl font-black font-mono text-amber-900" x-text="countdownFormatted">--:--</span>
                </div>
            </div>
        @endif

        <!-- Booking Details & Breakdown -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Venue & Slot Information -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Informasi Lapangan & Waktu</h3>
                
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block">Nama Venue</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $booking->court->venue->name }}</span>
                        <span class="text-slate-500 block">{{ $booking->court->venue->address }}</span>
                    </div>

                    <div class="border-t border-slate-200/60 pt-2 flex justify-between">
                        <div>
                            <span class="text-slate-400 block">Lapangan</span>
                            <span class="font-bold text-emerald-800">{{ $booking->court->name }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-slate-400 block">Cabang Olahraga</span>
                            <span class="font-semibold text-slate-700 uppercase font-mono">{{ $booking->court->sport_type }}</span>
                        </div>
                    </div>

                    <div class="border-t border-slate-200/60 pt-2">
                        <span class="text-slate-400 block mb-1">Daftar Slot Waktu Dipesan:</span>
                        <div class="space-y-1">
                            @foreach($booking->bookingSlots as $slot)
                                <div class="flex justify-between items-center bg-white px-3 py-1.5 rounded-lg border border-slate-200 font-mono text-xs">
                                    <span class="font-bold text-slate-800">
                                        {{ \Carbon\Carbon::parse($slot->slot_start)->setTimezone('Asia/Jakarta')->isoFormat('D MMM Y') }}
                                        · {{ \Carbon\Carbon::parse($slot->slot_start)->setTimezone('Asia/Jakarta')->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->slot_end)->setTimezone('Asia/Jakarta')->format('H:i') }}
                                    </span>
                                    <span class="text-emerald-700 font-semibold">Rp {{ number_format($slot->price, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($booking->notes)
                        <div class="border-t border-slate-200/60 pt-2">
                            <span class="text-slate-400 block">Catatan Tambahan</span>
                            <span class="text-slate-700 italic">{{ $booking->notes }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment Summary & Actions -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Rincian Pembayaran</h3>

                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5 space-y-3 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Total Biaya Sewa ({{ $booking->bookingSlots->count() }} Slot)</span>
                        <span class="font-mono font-bold text-slate-800">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-emerald-800 font-bold border-t border-slate-200/60 pt-2">
                        <span>DP Di Muka Wajib Dibayar ({{ $booking->court->venue->dp_percent }}%)</span>
                        <span class="font-mono text-lg text-emerald-600 font-black">Rp {{ number_format($booking->dp_amount, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-slate-400 text-[11px]">
                        <span>Sisa Pelunasan di Lokasi</span>
                        <span class="font-mono">Rp {{ number_format($booking->total_price - $booking->dp_amount, 0, ',', '.') }}</span>
                    </div>

                    @if($booking->refund_amount > 0)
                        <div class="flex justify-between text-amber-800 bg-amber-100/60 p-2 rounded-lg font-bold">
                            <span>Dana Dikembalikan (Refund)</span>
                            <span class="font-mono">Rp {{ number_format($booking->refund_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Action Buttons according to status -->
                @if($booking->isPendingPayment() && $payment)
                    <div class="space-y-3 pt-2">
                        <!-- Midtrans Snap Popup Button -->
                        <button type="button" @click="payWithSnap()"
                            class="w-full rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 py-3.5 px-6 font-bold text-white shadow-xl shadow-emerald-600/25 hover:from-emerald-700 hover:to-teal-700 transition flex items-center justify-center gap-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Bayar DP via Midtrans Snap (Rp {{ number_format($booking->dp_amount, 0, ',', '.') }})</span>
                        </button>

                        <!-- Sandbox Simulator Button (PRD Requirement Testing Support) -->
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5 text-center">
                            <span class="block text-[11px] font-semibold text-slate-500 mb-2">Simulasi Pengujian Sandbox Midtrans:</span>
                            <button type="button" @click="simulateSandboxSettlement()"
                                class="w-full rounded-xl bg-slate-900 py-2.5 px-4 font-semibold text-white text-xs hover:bg-slate-800 transition flex items-center justify-center gap-2">
                                <span>⚡ Simulasi Pembayaran Sukses (Webhook Instant)</span>
                            </button>
                            <p class="text-[10px] text-slate-400 mt-1.5">Mengirim webhook resmi Midtrans dengan validasi HMAC SHA-512 signature.</p>
                        </div>
                    </div>
                @elseif($booking->isConfirmed())
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 text-center space-y-3">
                        <div class="flex justify-center">
                            <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                        <h4 class="font-bold text-emerald-950 text-base">Pembayaran DP Berhasil Diterima</h4>
                        <p class="text-xs text-emerald-800 max-w-sm mx-auto">
                            Slot lapangan Anda telah terkonfirmasi. Tunjukkan kode booking <strong>{{ $booking->code }}</strong> kepada pengelola venue saat hadir.
                        </p>
                        <div class="pt-2 flex justify-center gap-3">
                            <a href="{{ route('my.bookings') }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition">
                                Lihat Booking Saya
                            </a>
                            <a href="{{ route('home') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                Pesan Lapangan Lain
                            </a>
                        </div>
                    </div>
                @elseif($booking->isExpired())
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center space-y-3">
                        <p class="text-xs text-slate-500">Batas waktu pembayaran telah habis. Slot telah dilepaskan kembali.</p>
                        <a href="{{ route('venues.show', $booking->court->venue_id) }}" class="inline-block rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition">
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
