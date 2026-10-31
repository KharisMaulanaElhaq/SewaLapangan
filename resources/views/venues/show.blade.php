@extends('layouts.app')

@section('title', $venue->name . ' — Jadwal & Booking Lapangan')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-8" x-data="slotBooking()">
    <!-- Breadcrumb -->
    <nav class="flex text-xs text-slate-500 gap-2 items-center">
        <a href="{{ route('home') }}" class="hover:text-emerald-600">Beranda</a>
        <span>/</span>
        <span class="text-slate-800 font-semibold">{{ $venue->name }}</span>
    </nav>

    <!-- Venue Header Banner -->
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="relative h-64 sm:h-80 w-full overflow-hidden bg-slate-900">
            <img src="{{ $venue->image_url ?: 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1400&q=80' }}"
                alt="{{ $venue->name }}"
                class="h-full w-full object-cover opacity-90">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>

            <div class="absolute bottom-6 left-6 right-6 text-white flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <span class="rounded-full bg-emerald-500/80 backdrop-blur-md px-3 py-1 text-xs font-bold text-white">
                        📍 {{ $venue->city }}
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mt-2">{{ $venue->name }}</h1>
                    <p class="text-sm text-slate-300 mt-1 max-w-2xl">{{ $venue->address }} · Telp: {{ $venue->phone }}</p>
                </div>

                <!-- Venue Rules Snapshot Highlight -->
                <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md rounded-2xl p-3 border border-white/15">
                    <div class="text-right">
                        <span class="block text-[10px] text-slate-300 uppercase font-semibold">Ketentuan DP</span>
                        <span class="text-lg font-bold text-emerald-400 font-mono">{{ $venue->dp_percent }}%</span>
                    </div>
                    <div class="h-8 w-px bg-white/20"></div>
                    <div class="text-right">
                        <span class="block text-[10px] text-slate-300 uppercase font-semibold">Batal Gratis</span>
                        <span class="text-lg font-bold text-white font-mono">H-{{ $venue->cancel_free_hours }} Jam</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 text-sm text-slate-600 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row justify-between gap-4">
            <p class="max-w-3xl leading-relaxed">{{ $venue->description }}</p>
            <div class="flex items-center gap-2 self-start text-xs text-slate-500">
                <span class="font-semibold text-slate-700">Pengelola:</span> {{ $venue->owner->name ?? 'Mitra Venue' }}
            </div>
        </div>
    </div>

    <!-- Court Selection Tabs & Date Picker -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Slot Calendar Grid (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Court Selector Tabs -->
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">1. Pilih Lapangan</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($venue->courts as $court)
                        <a href="{{ route('venues.show', ['id' => $venue->id, 'court_id' => $court->id, 'date' => $selectedDate]) }}"
                            class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold border transition {{ ($selectedCourt && $selectedCourt->id === $court->id) ? 'border-emerald-600 bg-emerald-50 text-emerald-900 shadow-sm' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                            <span class="h-2 w-2 rounded-full {{ ($selectedCourt && $selectedCourt->id === $court->id) ? 'bg-emerald-600' : 'bg-slate-300' }}"></span>
                            <span>{{ $court->name }}</span>
                            <span class="text-xs text-slate-400 font-mono">({{ strtoupper($court->sport_type) }})</span>
                        </a>
                    @endforeach
                </div>
            </div>

            @if($selectedCourt)
                <!-- Selected Court Photo & Specifications -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Visual & Spesifikasi Lapangan</span>
                            <h4 class="text-base font-extrabold text-slate-900">{{ $selectedCourt->name }}</h4>
                        </div>
                        <span class="rounded-lg bg-emerald-50 border border-emerald-200 px-2.5 py-1 text-xs font-bold text-emerald-800 uppercase font-mono">
                            {{ strtoupper(str_replace('_', ' ', $selectedCourt->sport_type)) }}
                        </span>
                    </div>

                    <!-- Photo Banner -->
                    <div class="relative h-48 sm:h-64 w-full overflow-hidden rounded-xl bg-slate-900 shadow-md">
                        <img src="{{ $selectedCourt->primary_image_url }}" alt="{{ $selectedCourt->name }}"
                            class="h-full w-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent"></div>
                        <div class="absolute bottom-3 left-4 text-white">
                            <span class="text-[11px] font-medium text-slate-300">Spesifikasi Unit:</span>
                            <p class="text-base font-bold text-white">{{ $selectedCourt->name }} • {{ $venue->name }}</p>
                        </div>
                    </div>

                    <!-- Gallery Thumbnails if multiple photos -->
                    @if($selectedCourt->images && count($selectedCourt->images) > 1)
                        <div class="space-y-1.5 pt-1">
                            <span class="text-[11px] font-semibold text-slate-500 block">Galeri Foto Lapangan ({{ count($selectedCourt->images) }} Foto):</span>
                            <div class="flex items-center gap-2.5 overflow-x-auto pb-1">
                                @foreach($selectedCourt->images as $img)
                                    <a href="{{ str_starts_with($img, 'http') ? $img : asset($img) }}" target="_blank"
                                        class="group relative h-16 w-24 flex-shrink-0 overflow-hidden rounded-xl border border-slate-200 hover:border-emerald-500 shadow-sm transition">
                                        <img src="{{ str_starts_with($img, 'http') ? $img : asset($img) }}" class="h-full w-full object-cover group-hover:scale-105 transition duration-200">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif


            <!-- Date Selector (Next 7 Days Quick Pills) -->
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">2. Pilih Tanggal Jadwal</h3>
                    <input type="date" value="{{ $selectedDate }}" min="{{ date('Y-m-d') }}"
                        @change="changeDate($event.target.value)"
                        class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700 focus:border-emerald-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-4 sm:grid-cols-7 gap-2">
                    @for($i = 0; $i < 7; $i++)
                        @php
                            $loopDate = \Carbon\Carbon::now('Asia/Jakarta')->addDays($i);
                            $loopDateStr = $loopDate->toDateString();
                            $isSelected = ($selectedDate === $loopDateStr);
                        @endphp
                        <a href="{{ route('venues.show', ['id' => $venue->id, 'court_id' => $selectedCourt?->id, 'date' => $loopDateStr]) }}"
                            class="flex flex-col items-center justify-center rounded-xl p-2.5 border text-center transition {{ $isSelected ? 'border-emerald-600 bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
                            <span class="text-[10px] font-bold uppercase tracking-wider {{ $isSelected ? 'text-emerald-100' : 'text-slate-400' }}">
                                {{ $loopDate->isoFormat('ddd') }}
                            </span>
                            <span class="text-base font-extrabold my-0.5">{{ $loopDate->format('d') }}</span>
                            <span class="text-[10px] {{ $isSelected ? 'text-emerald-100' : 'text-slate-500' }}">
                                {{ $loopDate->isoFormat('MMM') }}
                            </span>
                        </a>
                    @endfor
                </div>
            </div>

            <!-- Real-time Slot Availability Grid -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">3. Pilih Slot Waktu</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Klik slot untuk memilih. Anda dapat memilih satu atau beberapa slot berurutan.</p>
                    </div>

                    <!-- Legend Indicator -->
                    <div class="flex flex-wrap items-center gap-3 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="h-3 w-3 rounded-md bg-emerald-500"></span>
                            <span class="text-slate-600 font-medium">Tersedia</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="h-3 w-3 rounded-md bg-amber-400"></span>
                            <span class="text-slate-600 font-medium">Di-HOLD (15 m)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="h-3 w-3 rounded-md bg-slate-300"></span>
                            <span class="text-slate-400 font-medium">Terisi</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="h-3 w-3 rounded-md bg-slate-800"></span>
                            <span class="text-slate-500 font-medium">Maintenance</span>
                        </div>
                    </div>
                </div>

                @if(!$availability || empty($availability['slots']))
                    <div class="py-12 text-center text-slate-400">
                        <p>Tidak ada slot operasional untuk hari ini.</p>
                    </div>
                @else
                    <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        @foreach($availability['slots'] as $slot)
                            @php
                                $status = $slot['status'];
                                $isPast = $slot['is_past'];
                            @endphp

                            @if($status === 'available' && !$isPast)
                                <button type="button"
                                    @click="toggleSlot('{{ $slot['slot_start_iso'] }}', {{ $slot['price'] }}, '{{ $slot['start_time'] }} - {{ $slot['end_time'] }}')"
                                    :class="isSelected('{{ $slot['slot_start_iso'] }}') 
                                        ? 'border-emerald-600 bg-emerald-600 text-white ring-2 ring-emerald-600 ring-offset-2 shadow-lg shadow-emerald-600/30' 
                                        : 'border-emerald-200 bg-emerald-50/50 text-emerald-950 hover:bg-emerald-100 hover:border-emerald-300'"
                                    class="group relative flex flex-col items-center justify-center rounded-xl border p-3.5 transition">
                                    <span class="text-sm font-bold font-mono">{{ $slot['start_time'] }} - {{ $slot['end_time'] }}</span>
                                    <span class="mt-1 text-xs font-semibold" :class="isSelected('{{ $slot['slot_start_iso'] }}') ? 'text-emerald-100' : 'text-emerald-700'">
                                        Rp {{ number_format($slot['price'], 0, ',', '.') }}
                                    </span>
                                    <span class="mt-1 text-[10px] uppercase font-bold tracking-wider" :class="isSelected('{{ $slot['slot_start_iso'] }}') ? 'text-white' : 'text-emerald-600'">
                                        <span x-show="isSelected('{{ $slot['slot_start_iso'] }}')">✓ Terpilih</span>
                                        <span x-show="!isSelected('{{ $slot['slot_start_iso'] }}')">Tersedia</span>
                                    </span>
                                </button>
                            @elseif($status === 'held')
                                <div class="relative flex flex-col items-center justify-center rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-amber-900 cursor-not-allowed opacity-80"
                                    title="Slot sedang di-hold menunggu pembayaran DP pelanggan lain">
                                    <span class="text-sm font-bold font-mono">{{ $slot['start_time'] }} - {{ $slot['end_time'] }}</span>
                                    <span class="mt-1 text-xs font-semibold text-amber-700">Rp {{ number_format($slot['price'], 0, ',', '.') }}</span>
                                    <span class="mt-1 text-[10px] font-bold text-amber-800 bg-amber-200/80 px-2 py-0.5 rounded-full">
                                        ⏳ Di-HOLD (15m)
                                    </span>
                                </div>
                            @elseif($status === 'blocked')
                                <div class="relative flex flex-col items-center justify-center rounded-xl border border-slate-700 bg-slate-800 p-3.5 text-slate-300 cursor-not-allowed"
                                    title="Perawatan lapangan / Event khusus">
                                    <span class="text-sm font-bold font-mono text-slate-200">{{ $slot['start_time'] }} - {{ $slot['end_time'] }}</span>
                                    <span class="mt-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                        ⛔ Maintenance
                                    </span>
                                </div>
                            @else
                                <div class="relative flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-100 p-3.5 text-slate-400 cursor-not-allowed"
                                    title="{{ $isPast ? 'Waktu slot telah lewat' : 'Slot telah dipesan' }}">
                                    <span class="text-sm font-bold font-mono line-through">{{ $slot['start_time'] }} - {{ $slot['end_time'] }}</span>
                                    <span class="mt-1 text-[10px] font-semibold uppercase tracking-wider">
                                        {{ $isPast ? 'Lewat' : 'Terisi' }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Sticky Order Summary & Booking Action -->
        <div class="lg:col-span-1">
            <div class="sticky top-24 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">
                <h3 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3">Ringkasan Booking</h3>

                <!-- Selected Venue & Court Info -->
                <div class="mt-4 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Venue:</span>
                        <span class="font-bold text-slate-800 text-right">{{ $venue->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Lapangan:</span>
                        <span class="font-bold text-emerald-700 text-right">{{ $selectedCourt->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tanggal:</span>
                        <span class="font-semibold text-slate-800 text-right font-mono">{{ \Carbon\Carbon::parse($selectedDate)->isoFormat('dddd, D MMMM Y') }}</span>
                    </div>
                </div>

                <!-- Selected Slots List -->
                <div class="mt-5 border-t border-slate-100 pt-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-slate-700">Slot Terpilih:</span>
                        <span class="text-xs font-semibold text-emerald-600 font-mono" x-text="selectedSlots.length + ' Jam'"></span>
                    </div>

                    <template x-if="selectedSlots.length === 0">
                        <p class="text-xs text-slate-400 italic py-3 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                            Silakan klik slot waktu yang tersedia pada jadwal di sebelah kiri.
                        </p>
                    </template>

                    <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1" x-show="selectedSlots.length > 0">
                        <template x-for="(slot, idx) in selectedSlots" :key="slot.start">
                            <div class="flex items-center justify-between rounded-lg bg-emerald-50 px-3 py-1.5 text-xs text-emerald-950 border border-emerald-100">
                                <span class="font-bold font-mono" x-text="slot.label"></span>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-emerald-800" x-text="'Rp ' + Number(slot.price).toLocaleString('id-ID')"></span>
                                    <button type="button" @click="removeSlot(slot.start)" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Price & DP Breakdown -->
                <div class="mt-6 border-t border-slate-100 pt-4 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Total Biaya Sewa:</span>
                        <span class="font-bold text-slate-900 font-mono text-sm" x-text="'Rp ' + Number(totalPrice).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex justify-between text-emerald-700">
                        <span class="font-semibold">DP Di Muka ({{ $venue->dp_percent }}%):</span>
                        <span class="font-extrabold font-mono text-base" x-text="'Rp ' + Number(dpAmount).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex justify-between text-slate-400 text-[11px]">
                        <span>Sisa Pelunasan di Lokasi:</span>
                        <span class="font-mono" x-text="'Rp ' + Number(totalPrice - dpAmount).toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <!-- Booking Submission Form -->
                <form action="{{ route('venues.book') }}" method="POST" class="mt-6">
                    @csrf
                    <input type="hidden" name="court_id" value="{{ $selectedCourt->id }}">
                    <template x-for="slot in selectedSlots" :key="slot.start">
                        <input type="hidden" name="slots[]" :value="slot.start">
                    </template>

                    <div class="mb-4">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Catatan Tambahan (Opsional)</label>
                        <input type="text" name="notes" placeholder="Contoh: Sparring tim A vs tim B"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-emerald-500 focus:outline-none">
                    </div>

                    @guest
                        <a href="{{ route('login') }}" class="block w-full text-center rounded-xl bg-slate-900 py-3 font-bold text-white text-sm shadow-md hover:bg-slate-800 transition">
                            Login untuk Melanjutkan
                        </a>
                    @else
                        <button type="submit"
                            :disabled="selectedSlots.length === 0"
                            :class="selectedSlots.length === 0 ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-600/30'"
                            class="w-full rounded-xl py-3 font-bold text-white text-sm transition flex items-center justify-center gap-2">
                            <span>Lanjut ke Pembayaran DP &rarr;</span>
                        </button>
                    @endguest
                </form>

                <div class="mt-4 rounded-xl bg-slate-50 p-3 text-[11px] text-slate-500 leading-normal border border-slate-100">
                    🔒 Slot otomatis di-<strong>HOLD selama 15 menit</strong> setelah tombol ditekan untuk memberi waktu pembayaran DP melalui Midtrans.
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function slotBooking() {
        return {
            selectedSlots: [],
            dpPercent: {{ $venue->dp_percent }},
            
            get totalPrice() {
                return this.selectedSlots.reduce((sum, s) => sum + Number(s.price), 0);
            },

            get dpAmount() {
                return Math.round((this.totalPrice * this.dpPercent) / 100);
            },

            isSelected(startIso) {
                return this.selectedSlots.some(s => s.start === startIso);
            },

            toggleSlot(startIso, price, label) {
                if (this.isSelected(startIso)) {
                    this.removeSlot(startIso);
                } else {
                    this.selectedSlots.push({ start: startIso, price: price, label: label });
                    // Sort slots chronologically
                    this.selectedSlots.sort((a, b) => new Date(a.start) - new Date(b.start));
                }
            },

            removeSlot(startIso) {
                this.selectedSlots = this.selectedSlots.filter(s => s.start !== startIso);
            },

            changeDate(newDate) {
                const url = new URL(window.location.href);
                url.searchParams.set('date', newDate);
                window.location.href = url.toString();
            }
        }
    }
</script>
@endpush
@endsection
