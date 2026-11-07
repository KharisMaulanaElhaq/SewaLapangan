@extends('layouts.app')

@section('title', 'Daftar Akun Baru')

@section('content')
<div class="mx-auto max-w-lg px-4 py-12 sm:px-6 space-y-6" x-data="{ role: 'penyewa', isRegistering: false }">
    <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-soft-xl">
        <div class="text-center space-y-1">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200/80 mb-2 shadow-soft-xs">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-ink-900">Buat Akun Baru</h1>
            <p class="text-xs sm:text-sm text-slate-500">Daftar sebagai penyewa lapangan atau mitra pengelola venue</p>
        </div>

        <form action="{{ route('register') }}" method="POST" @submit="isRegistering = true" class="mt-6 space-y-4">
            @csrf

            <!-- Role Selector with Clean Inline SVGs -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Daftar Sebagai:</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2.5 rounded-2xl border-2 p-3.5 cursor-pointer transition-all duration-200 min-h-[48px]"
                        :class="role === 'penyewa' ? 'border-emerald-600 bg-emerald-50/70 text-emerald-950 font-bold shadow-soft-xs' : 'border-slate-200 bg-slate-50/50 text-slate-700 hover:bg-slate-100'">
                        <input type="radio" name="role" value="penyewa" x-model="role" class="text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                        <div class="flex items-center gap-1.5 text-xs">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>Penyewa (User)</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-2.5 rounded-2xl border-2 p-3.5 cursor-pointer transition-all duration-200 min-h-[48px]"
                        :class="role === 'pengelola' ? 'border-emerald-600 bg-emerald-50/70 text-emerald-950 font-bold shadow-soft-xs' : 'border-slate-200 bg-slate-50/50 text-slate-700 hover:bg-slate-100'">
                        <input type="radio" name="role" value="pengelola" x-model="role" class="text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                        <div class="flex items-center gap-1.5 text-xs">
                            <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>Pengelola Venue</span>
                        </div>
                    </label>
                </div>

                <div x-show="role === 'pengelola'" x-cloak class="mt-2.5 rounded-2xl bg-amber-50 border border-amber-200 p-3 text-xs text-amber-900 leading-relaxed flex items-start gap-2 shadow-soft-xs">
                    <svg class="h-4 w-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <strong>Catatan Verifikasi (FR-03):</strong> Pendaftaran akun Pengelola memerlukan proses review & verifikasi oleh Admin Platform sebelum dapat login dan mengelola venue.
                    </div>
                </div>
            </div>

            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition min-h-[44px]"
                    placeholder="Nama lengkap sesuai identitas">
                @error('name')
                    <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Alamat Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition min-h-[44px]"
                        placeholder="nama@email.com">
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Nomor WhatsApp / HP</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx"
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition min-h-[44px]">
                    @error('phone')
                        <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Kata Sandi (Min 8)</label>
                    <input type="password" id="password" name="password" required
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition min-h-[44px]"
                        placeholder="••••••••">
                    @error('password')
                        <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Ulangi Kata Sandi</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition min-h-[44px]"
                        placeholder="••••••••">
                </div>
            </div>

            <button type="submit" :disabled="isRegistering"
                class="w-full rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 py-3 font-bold text-white text-sm shadow-md shadow-emerald-600/25 hover:from-emerald-700 hover:to-teal-700 active:scale-[0.98] transition flex items-center justify-center gap-2 min-h-[44px] disabled:opacity-60 disabled:cursor-wait">
                
                <svg x-show="isRegistering" x-cloak class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>

                <span x-text="isRegistering ? 'Memproses Pendaftaran...' : 'Daftar Sekarang &rarr;'"></span>
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-500 border-t border-slate-100 pt-4">
            Sudah memiliki akun?
            <a href="{{ route('login') }}" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline ml-1">Masuk di sini</a>
        </div>
    </div>
</div>
@endsection
