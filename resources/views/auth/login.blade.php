@extends('layouts.app')

@section('title', 'Masuk ke Platform Booking Lapangan')

@section('content')
<div class="mx-auto max-w-md px-4 py-12 sm:px-6 space-y-6" x-data="{
    isLoggingIn: false,
    fillCreds(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }
}">
    <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-soft-xl">
        <div class="text-center space-y-1">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200/80 mb-2 shadow-soft-xs">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-ink-900">Masuk ke Akun Anda</h1>
            <p class="text-xs sm:text-sm text-slate-500">Gunakan akun penyewa, pengelola venue, atau admin platform</p>
        </div>

        <!-- Quick 1-Click Demo Login Bar with Clean Inline SVGs -->
        <div class="mt-6 rounded-2xl bg-slate-50/90 border border-slate-200/80 p-3.5 text-center space-y-2">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Cepat Uji Coba Demo:</span>
            <div class="grid grid-cols-3 gap-1.5">
                <button type="button" @click="fillCreds('arif.penyewa@gmail.com', 'password123')"
                    class="rounded-xl bg-white border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 px-2 py-2 text-[11px] font-bold text-slate-700 hover:text-emerald-800 shadow-soft-xs transition flex flex-col items-center gap-1 min-h-[44px] justify-center">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Penyewa</span>
                </button>

                <button type="button" @click="fillCreds('budi.pengelola@gmail.com', 'password123')"
                    class="rounded-xl bg-white border border-slate-200 hover:border-blue-500 hover:bg-blue-50/50 px-2 py-2 text-[11px] font-bold text-slate-700 hover:text-blue-800 shadow-soft-xs transition flex flex-col items-center gap-1 min-h-[44px] justify-center">
                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>Pengelola</span>
                </button>

                <button type="button" @click="fillCreds('admin@harkat.ac.id', 'password123')"
                    class="rounded-xl bg-white border border-slate-200 hover:border-purple-500 hover:bg-purple-50/50 px-2 py-2 text-[11px] font-bold text-slate-700 hover:text-purple-800 shadow-soft-xs transition flex flex-col items-center gap-1 min-h-[44px] justify-center">
                    <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Admin</span>
                </button>
            </div>
        </div>

        <form action="{{ route('login') }}" method="POST" @submit="isLoggingIn = true" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Alamat Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition min-h-[44px]"
                    placeholder="nama@email.com">
                @error('email')
                    <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Kata Sandi</label>
                <input type="password" id="password" name="password" required
                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition min-h-[44px]"
                    placeholder="••••••••">
                @error('password')
                    <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 text-slate-600 cursor-pointer min-h-[36px]">
                    <input type="checkbox" name="remember" class="rounded-md border-slate-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                    <span class="font-medium">Ingat saya di perangkat ini</span>
                </label>
            </div>

            <button type="submit" :disabled="isLoggingIn"
                class="w-full rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 py-3 font-bold text-white text-sm shadow-md shadow-emerald-600/25 hover:from-emerald-700 hover:to-teal-700 active:scale-[0.98] transition flex items-center justify-center gap-2 min-h-[44px] disabled:opacity-60 disabled:cursor-wait">
                
                <svg x-show="isLoggingIn" x-cloak class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>

                <span x-text="isLoggingIn ? 'Memproses Masuk...' : 'Masuk Sekarang &rarr;'"></span>
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-500 border-t border-slate-100 pt-4">
            Belum memiliki akun?
            <a href="{{ route('register') }}" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline ml-1">Daftar akun baru di sini</a>
        </div>
    </div>
</div>
@endsection
