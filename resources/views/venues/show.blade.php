@extends('layouts.app')

@section('title', $venue->name . ' — Jadwal & Booking Lapangan')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-8 pb-28 lg:pb-12" x-data="slotBooking()">
    <!-- Breadcrumb -->
    <nav class="flex text-xs text-slate-500 gap-2 items-center" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-emerald-600 transition flex items-center gap-1 font-medium">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Beranda</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-ink-900 font-bold truncate max-w-xs">{{ $venue->name }}</span>
    </nav>

    <!-- Venue Header Banner -->
    <div class="overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-soft-sm">
        <div class="relative h-64 sm:h-80 w-full overflow-hidden bg-ink-950">
            <img src="{{ $venue->image_url ?: 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1400&q=80' }}"
                alt="{{ $venue->name }}"
                class="h-full w-full object-cover opacity-85">
            <div class="absolute inset-0 bg-gradient-to-t from-ink-950/95 via-ink-950/40 to-transparent"></div>

            <div class="absolute bottom-6 left-6 right-6 text-white flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/90 backdrop-blur-md px-3.5 py-1 text-xs font-bold text-white shadow-soft-xs">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        {{ $venue->city }}
                    </span>
                    <h1 class="text-2xl sm:text-4xl font-black tracking-tight mt-2 drop-shadow-sm">{{ $venue->name }}</h1>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl">{{ $venue->address }} · Telp: {{ $venue->phone }}</p>
                </div>

                <!-- Venue Rules Snapshot Highlight -->
                <div class="flex items-center gap-4 bg-ink-900/80 backdrop-blur-md rounded-2xl p-3.5 border border-white/15 shadow-soft-md">
                    <div class="text-right">
                        <span class="block text-[10px] text-slate-300 uppercase font-bold tracking-wider">Ketentuan DP</span>
                        <span class="text-base sm:text-lg font-black text-emerald-400 font-mono">{{ $venue->dp_percent }}%</span>
                    </div>
                    <div class="h-8 w-px bg-white/20"></div>
                    <div class="text-right">
                        <span class="block text-[10px] text-slate-300 uppercase font-bold tracking-wider">Batal Gratis</span>
                        <span class="text-base sm:text-lg font-black text-white font-mono">H-{{ $venue->cancel_free_hours }} Jam</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-5 sm:p-6 text-xs sm:text-sm text-slate-600 bg-slate-50/80 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <p class="max-w-3xl leading-relaxed">{{ $venue->description }}</p>
            <div class="flex items-center gap-2 text-xs text-slate-500 rounded-xl bg-white px-3 py-1.5 border border-slate-200">
                <span class="font-bold text-slate-700">Pengelola:</span>
                <span class="font-semibold text-emerald-700">{{ $venue->owner->name ?? 'Mitra Venue' }}</span>
            </div>
        </div>
    </div>

    <!-- Court Selection Tabs & Date Picker -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Slot Calendar Grid (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Court Selector Tabs -->
            <div class="rounded-3xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-soft-sm">
                <div class="flex items-center justify-between mb-3.5">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">1. Pilih Lapangan</h3>
                    <span class="text-[11px] text-slate-500">{{ $venue->courts->count() }} unit lapangan</span>
                </div>
                <div class="flex flex-wrap gap-2.5">
                    @foreach($venue->courts as $court)
                        <a href="{{ route('venues.show', ['id' => $venue->id, 'court_id' => $court->id, 'date' => $selectedDate]) }}"
                            class="flex items-center gap-2 rounded-2xl px-4 py-2.5 text-xs sm:text-sm font-bold border transition-all duration-200 min-h-[44px] {{ ($selectedCourt && $selectedCourt->id === $court->id) ? 'border-emerald-600 bg-emerald-50 text-emerald-950 shadow-soft-xs ring-2 ring-emerald-500/20' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-300' }}">
                            <span class="h-2 w-2 rounded-full {{ ($selectedCourt && $selectedCourt->id === $court->id) ? 'bg-emerald-600' : 'bg-slate-300' }}"></span>
                            <span>{{ $court->name }}</span>
                            <span class="text-xs text-slate-400 font-mono font-normal">({{ strtoupper($court->sport_type) }})</span>
                        </a>
                    @endforeach
                </div>
            </div>

            @if($selectedCourt)
                <!-- Selected Court Photo & Specifications -->
                <div class="rounded-3xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-soft-sm space-y-3.5">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Visual & Spesifikasi Lapangan</span>
                            <h4 class="text-base sm:text-lg font-black text-ink-900">{{ $selectedCourt->name }}</h4>
                        </div>
                        <span class="rounded-xl bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-bold text-emerald-800 uppercase font-mono">
                            {{ strtoupper(str_replace('_', ' ', $selectedCourt->sport_type)) }}
                        </span>
                    </div>

                    <!-- Photo Banner -->
                    <div class="relative h-48 sm:h-64 w-full overflow-hidden rounded-2xl bg-slate-900 shadow-soft-sm">
                        <img src="{{ $selectedCourt->primary_image_url }}" alt="{{ $selectedCourt->name }}"
                            class="h-full w-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-ink-950/80 via-transparent to-transparent"></div>
                        <div class="absolute bottom-3.5 left-4 text-white">
                            <span class="text-[11px] font-medium text-slate-300">Spesifikasi Lapangan:</span>
                            <p class="text-base font-bold text-white">{{ $selectedCourt->name }} • {{ $venue->name }}</p>
                        </div>
                    </div>

                    <!-- Gallery Thumbnails if multiple photos -->
                    @if($selectedCourt->images && count($selectedCourt->images) > 1)
                        <div class="space-y-1.5 pt-1">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Galeri Foto Lapangan ({{ count($selectedCourt->images) }} Foto):</span>
                            <div class="flex items-center gap-2.5 overflow-x-auto pb-1.5">
                                @foreach($selectedCourt->images as $img)
                                    <a href="{{ str_starts_with($img, 'http') ? $img : asset($img) }}" target="_blank"
                                        class="group relative h-16 w-24 flex-shrink-0 overflow-hidden rounded-xl border border-slate-200 hover:border-emerald-500 shadow-soft-xs transition-all">
                                        <img src="{{ str_starts_with($img, 'http') ? $img : asset($img) }}" class="h-full w-full object-cover group-hover:scale-105 transition duration-300" alt="Foto Lapangan">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Date Selector (Next 7 Days Quick Pills - min 44px touch targets) -->
            <div class="rounded-3xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-soft-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">2. Pilih Tanggal Jadwal</h3>
                    <div class="flex items-center gap-2">
                        <label for="date-picker-input" class="text-xs text-slate-500 font-medium">Pilih manual:</label>
                        <input id="date-picker-input" type="date" value="{{ $selectedDate }}" min="{{ date('Y-m-d') }}"
                            @change="changeDate($event.target.value)"
                            class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 focus:border-emerald-500 focus:outline-none min-h-[40px]">
                    </div>
                </div>

                <!-- 7 Days Quick Select Grid -->
                <div class="grid grid-cols-4 sm:grid-cols-7 gap-2">
                    @for($i = 0; $i < 7; $i++)
                        @php
                            $loopDate = \Carbon\Carbon::now('Asia/Jakarta')->addDays($i);
                            $loopDateStr = $loopDate->toDateString();
                            $isSelected = ($selectedDate === $loopDateStr);
                        @endphp
                        <a href="{{ route('venues.show', ['id' => $venue->id, 'court_id' => $selectedCourt?->id, 'date' => $loopDateStr]) }}"
                            class="flex flex-col items-center justify-center rounded-2xl p-2.5 border text-center transition-all duration-200 min-h-[56px] {{ $isSelected ? 'border-emerald-600 bg-emerald-600 text-white shadow-md shadow-emerald-600/25 ring-2 ring-emerald-600/30 font-bold' : 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
                            <span class="text-[10px] font-bold uppercase tracking-wider {{ $isSelected ? 'text-emerald-100' : 'text-slate-400' }}">
                                {{ $loopDate->isoFormat('ddd') }}
                            </span>
                            <span class="text-base font-black my-0.5 font-mono">{{ $loopDate->format('d') }}</span>
                            <span class="text-[10px] {{ $isSelected ? 'text-emerald-100' : 'text-slate-500' }}">
                                {{ $loopDate->isoFormat('MMM') }}
                            </span>
                        </a>
                    @endfor
                </div>
            </div>

            <!-- Real-time Slot Availability Grid with Enhanced Legends -->
            <div class="rounded-3xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-soft-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base font-black text-ink-900">3. Pilih Slot Waktu</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Klik slot untuk memilih. Anda dapat memilih satu atau beberapa jam sekaligus.</p>
                    </div>

                    <!-- Enhanced Status Legend with Colors, Icons, and Text -->
                    <div class="flex flex-wrap items-center gap-3 text-xs">
                        <div class="flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2 py-1 border border-emerald-200">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span class="text-emerald-800 font-bold text-[11px]">Tersedia</span>
                        </div>
                        <div class="flex items-center gap-1.5 rounded-lg bg-amber-50 px-2 py-1 border border-amber-200">
                            <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                            <span class="text-amber-800 font-bold text-[11px]">Di-HOLD (15m)</span>
                        </div>
                        <div class="flex items-center gap-1.5 rounded-lg bg-slate-100 px-2 py-1 border border-slate-200">
                            <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                            <span class="text-slate-600 font-semibold text-[11px]">Terisi / Lewat</span>
                        </div>
                        <div class="flex items-center gap-1.5 rounded-lg bg-slate-800 px-2 py-1 border border-slate-700">
                            <span class="h-2 w-2 rounded-full bg-rose-400"></span>
                            <span class="text-slate-200 font-semibold text-[11px]">Maintenance</span>
                        </div>
                    </div>
                </div>

                @if(!$availability || empty($availability['slots']))
                    <div class="py-12 text-center text-slate-400">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="mt-2 text-sm font-semibold text-slate-600">Tidak ada slot operasional untuk hari ini.</p>
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
                                        ? 'border-emerald-600 bg-emerald-600 text-white ring-2 ring-emerald-600 ring-offset-2 shadow-md shadow-emerald-600/30' 
                                        : 'border-emerald-200 bg-emerald-50/50 text-emerald-950 hover:bg-emerald-100/70 hover:border-emerald-300'"
                                    class="group relative flex flex-col items-center justify-center rounded-2xl border p-3.5 transition-all duration-200 min-h-[64px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                                    
                                    <span class="text-sm font-black font-mono">{{ $slot['start_time'] }} - {{ $slot['end_time'] }}</span>
                                    <span class="mt-1 text-xs font-bold font-mono" :class="isSelected('{{ $slot['slot_start_iso'] }}') ? 'text-emerald-100' : 'text-emerald-700'">
                                        Rp {{ number_format($slot['price'], 0, ',', '.') }}
                                    </span>
                                    <span class="mt-1 text-[10px] uppercase font-bold tracking-wider" :class="isSelected('{{ $slot['slot_start_iso'] }}') ? 'text-white' : 'text-emerald-600'">
                                        <span x-show="isSelected('{{ $slot['slot_start_iso'] }}')">✓ Terpilih</span>
                                        <span x-show="!isSelected('{{ $slot['slot_start_iso'] }}')">Tersedia</span>
                                    </span>
                                </button>
                            @elseif($status === 'held')
                                <div class="relative flex flex-col items-center justify-center rounded-2xl border border-amber-200 bg-amber-50/80 p-3.5 text-amber-900 cursor-not-allowed opacity-90 min-h-[64px]"
                                    title="Slot sedang di-hold menunggu pembayaran DP pelanggan lain">
                                    <span class="text-sm font-bold font-mono text-amber-950">{{ $slot['start_time'] }} - {{ $slot['end_time'] }}</span>
                                    <span class="mt-1 text-xs font-semibold text-amber-700 font-mono">Rp {{ number_format($slot['price'], 0, ',', '.') }}</span>
                                    <span class="mt-1 text-[10px] font-bold text-amber-800 bg-amber-200/80 px-2 py-0.5 rounded-full">
                                        ⏳ Di-HOLD (15m)
                                    </span>
                                </div>
                            @elseif($status === 'blocked')
                                <div class="relative flex flex-col items-center justify-center rounded-2xl border border-slate-700 bg-slate-800 p-3.5 text-slate-300 cursor-not-allowed min-h-[64px]"
                                    title="Perawatan lapangan / Event khusus">
                                    <span class="text-sm font-bold font-mono text-slate-200">{{ $slot['start_time'] }} - {{ $slot['end_time'] }}</span>
                                    <span class="mt-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                        ⛔ Maintenance
                                    </span>
                                </div>
                            @else
                                <div class="relative flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-slate-100/80 p-3.5 text-slate-400 cursor-not-allowed min-h-[64px]"
                                    title="{{ $isPast ? 'Waktu slot telah lewat' : 'Slot telah dipesan' }}">
                                    <span class="text-sm font-bold font-mono line-through text-slate-400">{{ $slot['start_time'] }} - {{ $slot['end_time'] }}</span>
                                    <span class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                        {{ $isPast ? 'Lewat' : 'Terisi' }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Sticky Order Summary & Booking Action (Desktop) -->
        <div class="lg:col-span-1">
            <div class="sticky top-24 rounded-3xl border border-slate-200/90 bg-white p-6 shadow-soft-xl space-y-5">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <h3 class="text-lg font-black text-ink-900">Ringkasan Booking</h3>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-emerald-50 px-2.5 py-0.5 text-emerald-700 border border-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Real-Time
                    </span>
                </div>

                <!-- Selected Venue & Court Info -->
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">Venue:</span>
                        <span class="font-bold text-ink-900 text-right truncate max-w-[180px]">{{ $venue->name }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">Lapangan:</span>
                        <span class="font-bold text-emerald-700 text-right">{{ $selectedCourt->name }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">Tanggal:</span>
                        <span class="font-semibold text-slate-800 text-right font-mono">{{ \Carbon\Carbon::parse($selectedDate)->isoFormat('dddd, D MMMM Y') }}</span>
                    </div>
                </div>

                <!-- Selected Slots List -->
                <div class="border-t border-slate-100 pt-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Slot Terpilih:</span>
                        <span class="text-xs font-extrabold text-emerald-600 font-mono" x-text="selectedSlots.length + ' Jam'"></span>
                    </div>

                    <template x-if="selectedSlots.length === 0">
                        <p class="text-xs text-slate-400 italic py-3 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                            Silakan klik slot waktu yang tersedia pada jadwal di sebelah kiri.
                        </p>
                    </template>

                    <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1" x-show="selectedSlots.length > 0">
                        <template x-for="(slot, idx) in selectedSlots" :key="slot.start">
                            <div class="flex items-center justify-between rounded-xl bg-emerald-50/70 px-3 py-2 text-xs text-emerald-950 border border-emerald-100">
                                <span class="font-black font-mono" x-text="slot.label"></span>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-emerald-800" x-text="'Rp ' + Number(slot.price).toLocaleString('id-ID')"></span>
                                    <button type="button" @click="removeSlot(slot.start)" class="text-rose-500 hover:text-rose-700 font-bold p-1" aria-label="Hapus slot">&times;</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Price & DP Breakdown -->
                <div class="border-t border-slate-100 pt-4 space-y-2 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Total Biaya Sewa:</span>
                        <span class="font-bold text-ink-900 font-mono text-sm" x-text="'Rp ' + Number(totalPrice).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex justify-between items-center text-emerald-700">
                        <span class="font-bold">DP Di Muka ({{ $venue->dp_percent }}%):</span>
                        <span class="font-black font-mono text-base" x-text="'Rp ' + Number(dpAmount).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex justify-between items-center text-slate-400 text-[11px]">
                        <span>Sisa Pelunasan di Lokasi:</span>
                        <span class="font-mono" x-text="'Rp ' + Number(totalPrice - dpAmount).toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <!-- Booking Submission Form with Loading State -->
                <form action="{{ route('venues.book') }}" method="POST" id="bookingForm" class="pt-2"
                    x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                    @csrf
                    <input type="hidden" name="court_id" value="{{ $selectedCourt->id }}">
                    <template x-for="slot in selectedSlots" :key="slot.start">
                        <input type="hidden" name="slots[]" :value="slot.start">
                    </template>

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <input type="text" name="notes" placeholder="Contoh: Sparring tim futsal"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-emerald-500 focus:outline-none min-h-[40px]">
                    </div>

                    @guest
                        <a href="{{ route('login') }}" class="block w-full text-center rounded-xl bg-ink-900 py-3 font-bold text-white text-sm shadow-md hover:bg-slate-800 transition min-h-[44px] flex items-center justify-center">
                            Login untuk Melanjutkan
                        </a>
                    @else
                        <button type="submit"
                            :disabled="selectedSlots.length === 0 || isSubmitting"
                            :class="selectedSlots.length === 0 ? 'opacity-50 cursor-not-allowed bg-slate-300' : 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-600/25'"
                            class="w-full rounded-xl py-3 font-bold text-white text-sm transition flex items-center justify-center gap-2 min-h-[44px] active:scale-[0.98]">
                            
                            <svg x-show="isSubmitting" x-cloak class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>

                            <span x-text="isSubmitting ? 'Memproses Penguncian Slot...' : 'Lanjut ke Pembayaran DP &rarr;'"></span>
                        </button>
                    @endguest
                </form>

                <div class="rounded-2xl bg-slate-50 p-3 text-[11px] text-slate-500 leading-normal border border-slate-100 flex items-start gap-2">
                    <svg class="h-4 w-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>Slot otomatis di-<strong>HOLD 15 menit</strong> setelah konfirmasi untuk proses bayar DP Midtrans.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Sticky Bottom Floating Bar (Appears when slots selected on mobile < lg) -->
    <div x-show="selectedSlots.length > 0" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-0 left-0 right-0 z-30 lg:hidden border-t border-slate-200/90 bg-white/95 backdrop-blur-md px-4 py-3 shadow-soft-xl">
        
        <div class="flex items-center justify-between gap-3 max-w-md mx-auto">
            <div>
                <div class="flex items-center gap-1.5">
                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200"
                        x-text="selectedSlots.length + ' Slot Dipilih'"></span>
                </div>
                <div class="mt-0.5">
                    <span class="text-[10px] text-slate-400 block font-medium">DP Di Muka:</span>
                    <span class="text-base font-black text-emerald-600 font-mono leading-tight"
                        x-text="'Rp ' + Number(dpAmount).toLocaleString('id-ID')"></span>
                </div>
            </div>

            @guest
                <a href="{{ route('login') }}" class="rounded-xl bg-ink-900 px-5 py-2.5 text-xs font-bold text-white shadow-soft-xs min-h-[44px] flex items-center">
                    Login
                </a>
            @else
                <button type="submit" form="bookingForm"
                    class="rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-600/30 flex items-center gap-1.5 min-h-[44px] active:scale-[0.98]">
                    <span>Lanjut Bayar DP</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            @endguest
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
