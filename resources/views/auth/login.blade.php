@extends('layouts.app')

@section('title', 'Masuk ke Platform Booking Lapangan')

@section('content')
<div class="mx-auto max-w-md px-4 py-12 sm:px-6 space-y-6" x-data="{
    fillCreds(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }
}">
    <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-xl">
        <div class="text-center">
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Masuk ke Akun Anda</h1>
            <p class="text-xs text-slate-500 mt-1">Gunakan akun penyewa, pengelola venue, atau admin platform</p>
        </div>

        <!-- Quick 1-Click Demo Login Bar -->
        <div class="mt-6 rounded-2xl bg-slate-50 border border-slate-200/80 p-3 text-center space-y-2">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Cepat Uji Coba Demo:</span>
            <div class="flex flex-wrap justify-center gap-1.5">
                <button type="button" @click="fillCreds('arif.penyewa@gmail.com', 'password123')"
                    class="rounded-lg bg-emerald-100/70 hover:bg-emerald-200 px-2.5 py-1 text-[11px] font-bold text-emerald-800 transition">
                    👤 Penyewa
                </button>
                <button type="button" @click="fillCreds('budi.pengelola@gmail.com', 'password123')"
                    class="rounded-lg bg-blue-100/70 hover:bg-blue-200 px-2.5 py-1 text-[11px] font-bold text-blue-800 transition">
                    🏢 Pengelola
                </button>
                <button type="button" @click="fillCreds('admin@harkat.ac.id', 'password123')"
                    class="rounded-lg bg-purple-100/70 hover:bg-purple-200 px-2.5 py-1 text-[11px] font-bold text-purple-800 transition">
                    🛡️ Admin
                </button>
            </div>
        </div>

        <form action="{{ route('login') }}" method="POST" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-900 focus:border-emerald-500 focus:outline-none transition">
                @error('email')
                    <p class="mt-1 text-[11px] text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi</label>
                <input type="password" id="password" name="password" required
                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-900 focus:border-emerald-500 focus:outline-none transition">
                @error('password')
                    <p class="mt-1 text-[11px] text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit"
                class="w-full rounded-xl bg-emerald-600 py-3 font-bold text-white text-xs shadow-md shadow-emerald-600/30 hover:bg-emerald-700 transition">
                Masuk Sekarang &rarr;
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-500 border-t border-slate-100 pt-4">
            Belum memiliki akun?
            <a href="{{ route('register') }}" class="font-bold text-emerald-600 hover:underline">Daftar di sini</a>
        </div>
    </div>
</div>
@endsection
