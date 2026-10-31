<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Platform Booking Lapangan Olahraga') — Web Service</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN for zero-friction rendering) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Midtrans Snap JS (Sandbox) -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
    @livewireStyles
    @stack('styles')
</head>
<body class="flex min-h-full flex-col text-slate-800 selection:bg-emerald-500 selection:text-white">

    <!-- Header Navigation -->
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <!-- Brand Logo -->
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="group flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white shadow-md shadow-emerald-500/20 transition-transform group-hover:scale-105">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" stroke-width="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18M3 12h18"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5 font-bold tracking-tight text-slate-900 text-lg">
                            <span>SportVenue</span>
                        </div>
                        <p class="text-[11px] text-slate-500 font-medium">Platform Booking Lapangan</p>
                    </div>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-1">
                <a href="{{ route('home') }}" class="rounded-lg px-3.5 py-2 text-sm font-medium {{ request()->routeIs('home') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }} transition">
                    Cari Lapangan
                </a>

                @auth
                    @if(Auth::user()->isPenyewa())
                        <a href="{{ route('my.bookings') }}" class="rounded-lg px-3.5 py-2 text-sm font-medium {{ request()->routeIs('my.bookings') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }} transition">
                            Booking Saya
                        </a>
                    @endif

                    @if(Auth::user()->isPengelola() || Auth::user()->isAdmin())
                        <a href="{{ route('pengelola.dashboard') }}" class="rounded-lg px-3.5 py-2 text-sm font-medium {{ request()->routeIs('pengelola.dashboard') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }} transition">
                            Dashboard Pengelola
                        </a>
                    @endif

                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3.5 py-2 text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }} transition">
                            Admin Platform
                        </a>
                    @endif
                @endauth
            </nav>

            <!-- User Auth Profile / Buttons -->
            <div class="flex items-center gap-3">
                @guest
                    <a href="{{ route('login') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-600/30 hover:bg-emerald-700 transition">
                        Daftar
                    </a>
                @else
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-2.5 rounded-full border border-slate-200 bg-white py-1.5 pl-2 pr-3 text-left text-sm hover:border-slate-300 transition">
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-600 font-semibold text-xs text-white">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div class="hidden sm:block">
                                <p class="text-xs font-semibold leading-tight text-slate-900">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] uppercase font-bold text-emerald-600 tracking-wider">{{ Auth::user()->role }}</p>
                            </div>
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-56 rounded-xl border border-slate-200 bg-white py-1.5 shadow-xl">
                            <div class="border-b border-slate-100 px-4 py-2 text-xs">
                                <p class="font-bold text-slate-900">{{ Auth::user()->name }}</p>
                                <p class="text-slate-500 truncate">{{ Auth::user()->email }}</p>
                                <span class="mt-1 inline-block rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Role: {{ strtoupper(Auth::user()->role) }}</span>
                            </div>

                            @if(Auth::user()->isPenyewa())
                                <a href="{{ route('my.bookings') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Booking Saya</a>
                            @endif

                            @if(Auth::user()->isPengelola() || Auth::user()->isAdmin())
                                <a href="{{ route('pengelola.dashboard') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Dashboard Pengelola</a>
                            @endif

                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Admin Platform</a>
                            @endif

                            <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Pengaturan Profil</a>

                            <div class="border-t border-slate-100 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 font-medium">
                                        Keluar (Logout)
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endguest
            </div>
        </div>
    </header>

    <!-- Global Alert / Flash Messages -->
    <div class="mx-auto w-full max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 shadow-sm" role="alert">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 1118 0z"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 shadow-sm" role="alert">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 1118 0z"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900">&times;</button>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-4 flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 shadow-sm" role="alert">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 1118 0z"/>
                    </svg>
                    <span>{{ session('warning') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-amber-600 hover:text-amber-900">&times;</button>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white py-8 mt-16 text-slate-500">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
                <div>
                    <p class="font-bold text-slate-800 text-sm">PRD — Platform Booking Lapangan Olahraga</p>
                    <p class="mt-1">Mata Kuliah: Pemrograman Web Servis · Kelompok 3 · Teknik Informatika, Universitas Harkat Negeri · 2026</p>
                    <p class="text-slate-400 mt-0.5">Muhamad Djafar Ilyasa (23215028), Moh. Arif Prasetyo (23215043), Lutfi Laeli Nur Afiyah (23215046), Muh. Kharis Maulana Elhaq (23215059)</p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700 border border-emerald-200/60">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Laravel 12 & Livewire 3
                    </span>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>
</html>
