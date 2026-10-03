@extends('layouts.app')

@section('title', 'Daftar Akun Baru')

@section('content')
<div class="mx-auto max-w-lg px-4 py-12 sm:px-6 space-y-6" x-data="{ role: 'penyewa' }">
    <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-xl">
        <div class="text-center">
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Buat Akun Baru</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar sebagai penyewa lapangan atau mitra pengelola venue</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="mt-6 space-y-4">
            @csrf

            <!-- Role Selector -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Daftar Sebagai:</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 rounded-xl border p-3 cursor-pointer transition"
                        :class="role === 'penyewa' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 font-bold' : 'border-slate-200 bg-slate-50 text-slate-700'">
                        <input type="radio" name="role" value="penyewa" x-model="role" class="text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs">👤 Penyewa (User)</span>
                    </label>

                    <label class="flex items-center gap-2 rounded-xl border p-3 cursor-pointer transition"
                        :class="role === 'pengelola' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 font-bold' : 'border-slate-200 bg-slate-50 text-slate-700'">
                        <input type="radio" name="role" value="pengelola" x-model="role" class="text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs">🏢 Pengelola Venue</span>
                    </label>
                </div>

                <div x-show="role === 'pengelola'" x-cloak class="mt-2 rounded-xl bg-amber-50 border border-amber-200 p-2.5 text-[11px] text-amber-800 leading-normal">
                    ⚠️ <strong>Catatan FR-03:</strong> Pendaftaran akun Pengelola memerlukan proses verifikasi & persetujuan dari Admin Platform sebelum dapat login dan mengelola venue.
                </div>
            </div>

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-900 focus:border-emerald-500 focus:outline-none transition">
                @error('name')
                    <p class="mt-1 text-[11px] text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-900 focus:border-emerald-500 focus:outline-none transition">
                    @error('email')
                        <p class="mt-1 text-[11px] text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp / HP</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx"
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-900 focus:border-emerald-500 focus:outline-none transition">
                    @error('phone')
                        <p class="mt-1 text-[11px] text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi (Min 8)</label>
                    <input type="password" id="password" name="password" required
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-900 focus:border-emerald-500 focus:outline-none transition">
                    @error('password')
                        <p class="mt-1 text-[11px] text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">Ulangi Kata Sandi</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-900 focus:border-emerald-500 focus:outline-none transition">
                </div>
            </div>

            <button type="submit"
                class="w-full rounded-xl bg-emerald-600 py-3 font-bold text-white text-xs shadow-md shadow-emerald-600/30 hover:bg-emerald-700 transition">
                Daftar Sekarang &rarr;
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-500 border-t border-slate-100 pt-4">
            Sudah memiliki akun?
            <a href="{{ route('login') }}" class="font-bold text-emerald-600 hover:underline">Masuk di sini</a>
        </div>
    </div>
</div>
@endsection
