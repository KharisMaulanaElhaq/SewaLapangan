@extends('layouts.app')

@section('title', 'Pengaturan Profil Pengguna')

@section('content')
<div class="mx-auto max-w-2xl px-4 py-10 sm:px-6 space-y-6">
    <!-- Breadcrumb / Header Title -->
    <div>
        <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-2">
            <a href="{{ route('home') }}" class="hover:text-slate-800 transition">Beranda</a>
            <span>/</span>
            <span class="text-slate-800 font-bold">Pengaturan Profil</span>
        </nav>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">Pengaturan Profil Pengguna</h1>
        <p class="text-xs text-slate-500 mt-1">Perbarui data diri, nomor kontak, serta kata sandi akun Anda.</p>
    </div>

    <!-- User Profile Overview Card -->
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex items-center gap-4">
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-black text-2xl shadow-md shadow-emerald-500/20 flex-shrink-0">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-lg font-bold text-slate-900 truncate">{{ $user->name }}</h2>
                <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                    {{ ucfirst($user->role) }}
                </span>
                @if($user->status === 'active')
                    <span class="inline-flex items-center rounded-md bg-teal-50 px-2 py-0.5 text-xs font-semibold text-teal-700 ring-1 ring-inset ring-teal-600/20">
                        Aktif
                    </span>
                @elseif($user->status === 'pending')
                    <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-800 ring-1 ring-inset ring-amber-600/20">
                        Menunggu Verifikasi
                    </span>
                @else
                    <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-600/20">
                        Dinonaktifkan
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-0.5">{{ $user->email }} · Bergabung sejak {{ $user->created_at?->format('d M Y') ?? '-' }}</p>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-xl">
        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Informasi Dasar -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Informasi Akun & Kontak</h3>
                <div class="mt-4 space-y-4">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full rounded-xl border @error('name') border-rose-300 ring-rose-200 @else border-slate-200 @enderror px-3.5 py-2.5 text-xs text-slate-900 focus:border-emerald-500 focus:outline-none transition">
                        @error('name')
                            <p class="mt-1 text-[11px] text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">
                            Alamat Email (Akun Utama)
                        </label>
                        <input type="email" id="email" value="{{ $user->email }}" disabled
                            class="w-full rounded-xl border border-slate-200 bg-slate-100/70 px-3.5 py-2.5 text-xs text-slate-500 cursor-not-allowed">
                        <p class="mt-1 text-[11px] text-slate-400">Alamat email digunakan sebagai tanda pengenal akun unik Anda dan tidak dapat diubah.</p>
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1">
                            Nomor WhatsApp / HP <span class="text-rose-500">*</span>
                        </label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required placeholder="Contoh: 081234567890"
                            class="w-full rounded-xl border @error('phone') border-rose-300 ring-rose-200 @else border-slate-200 @enderror px-3.5 py-2.5 text-xs text-slate-900 focus:border-emerald-500 focus:outline-none transition">
                        @error('phone')
                            <p class="mt-1 text-[11px] text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Ganti Password -->
            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:p-5">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <h3 class="text-xs font-bold text-slate-900">Ubah Kata Sandi</h3>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">Kosongkan kolom kata sandi di bawah jika Anda tidak berniat mengubah kata sandi saat ini.</p>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">
                            Kata Sandi Baru (Min 8 Karakter)
                        </label>
                        <input type="password" id="password" name="password" placeholder="••••••••"
                            class="w-full rounded-xl border @error('password') border-rose-300 ring-rose-200 @else border-slate-200 @enderror bg-white px-3.5 py-2.5 text-xs text-slate-900 focus:border-emerald-500 focus:outline-none transition">
                        @error('password')
                            <p class="mt-1 text-[11px] text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">
                            Konfirmasi Kata Sandi Baru
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-900 focus:border-emerald-500 focus:outline-none transition">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                <a href="{{ route('home') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit"
                    class="rounded-xl bg-emerald-600 px-6 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-600/30 hover:bg-emerald-700 transition">
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

