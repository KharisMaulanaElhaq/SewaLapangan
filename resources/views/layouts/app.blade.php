<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Platform Booking Lapangan Olahraga') — Web Service</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN with Premium Luxury Design Tokens) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
                    },
                    colors: {
                        ink: {
                            800: '#141E33',
                            900: '#0B1220',
                            950: '#070B14',
                        },
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            900: '#064e3b',
                        },
                        gold: {
                            100: '#FBF5E8',
                            200: '#F4E7C7',
                            400: '#D4AF37',
                            500: '#C8A24A',
                            600: '#B38E38',
                            700: '#947228',
                        }
                    },
                    boxShadow: {
                        'soft-xs': '0 1px 2px 0 rgba(11, 18, 32, 0.05)',
                        'soft-sm': '0 2px 4px 0 rgba(11, 18, 32, 0.04), 0 1px 2px 0 rgba(11, 18, 32, 0.02)',
                        'soft-md': '0 6px 16px -2px rgba(11, 18, 32, 0.08), 0 2px 6px -1px rgba(11, 18, 32, 0.04)',
                        'soft-lg': '0 12px 28px -4px rgba(11, 18, 32, 0.10), 0 4px 10px -2px rgba(11, 18, 32, 0.05)',
                        'soft-xl': '0 20px 40px -6px rgba(11, 18, 32, 0.14), 0 8px 16px -4px rgba(11, 18, 32, 0.06)',
                    },
                    borderRadius: {
                        '2xl': '16px',
                        '3xl': '24px',
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
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
        .luxury-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .luxury-dark-glass {
            background: rgba(11, 18, 32, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
    </style>
    @livewireStyles
    @stack('styles')
</head>
<body class="flex min-h-full flex-col text-slate-800 bg-slate-50 selection:bg-emerald-500 selection:text-white" x-data="{ mobileMenuOpen: false }">

    <!-- Header Navigation -->
    <header class="sticky top-0 z-40 border-b border-slate-200/80 luxury-glass shadow-soft-xs">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <!-- Brand Logo -->
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="group flex items-center gap-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 rounded-xl p-0.5">
                    <img src="{{ asset('images/logo.png') }}"
                        alt="SportVenue Logo"
                        class="h-10 w-auto object-contain transition-transform duration-300 group-hover:scale-105">
                    <div>
                        <div class="flex items-center gap-1.5 font-black tracking-tight text-ink-900 text-lg">
                            <span>SportVenue</span>
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        </div>
                        <p class="text-[11px] text-slate-500 font-semibold tracking-wide">Sistem Booking Lapangan</p>
                    </div>
                </a>
            </div>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center gap-1.5">
                <a href="{{ route('home') }}" class="rounded-xl px-3.5 py-2 text-sm font-semibold transition-all duration-200 {{ request()->routeIs('home') ? 'bg-emerald-50 text-emerald-800 shadow-soft-xs font-bold' : 'text-slate-600 hover:text-ink-900 hover:bg-slate-100/80' }}">
                    Cari Lapangan
                </a>

                @auth
                    @if(Auth::user()->isPenyewa())
                        <a href="{{ route('my.bookings') }}" class="rounded-xl px-3.5 py-2 text-sm font-semibold transition-all duration-200 {{ request()->routeIs('my.bookings') ? 'bg-emerald-50 text-emerald-800 shadow-soft-xs font-bold' : 'text-slate-600 hover:text-ink-900 hover:bg-slate-100/80' }}">
                            Booking Saya
                        </a>
                    @endif

                    @if(Auth::user()->isPengelola() || Auth::user()->isAdmin())
                        <a href="{{ route('pengelola.dashboard') }}" class="rounded-xl px-3.5 py-2 text-sm font-semibold transition-all duration-200 {{ request()->routeIs('pengelola.dashboard') ? 'bg-emerald-50 text-emerald-800 shadow-soft-xs font-bold' : 'text-slate-600 hover:text-ink-900 hover:bg-slate-100/80' }}">
                            Dashboard Pengelola
                        </a>
                    @endif

                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-xl px-3.5 py-2 text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700 shadow-soft-xs font-bold' : 'text-slate-600 hover:text-ink-900 hover:bg-slate-100/80' }}">
                            Admin Platform
                        </a>
                    @endif
                @endauth

                <a href="{{ route('docs') }}" class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-sm font-semibold transition-all duration-200 {{ request()->routeIs('docs*') ? 'bg-emerald-50 text-emerald-800 shadow-soft-xs font-bold' : 'text-slate-600 hover:text-ink-900 hover:bg-slate-100/80' }}">
                    <span class="rounded-md bg-ink-900 text-[10px] font-bold text-white px-1.5 py-0.5 font-mono shadow-xs">API</span>
                    <span>Dokumentasi</span>
                </a>
            </nav>

            <!-- User Auth Profile / Desktop Buttons / Mobile Hamburger -->
            <div class="flex items-center gap-2.5">
                @guest
                    <div class="hidden sm:flex items-center gap-2">
                        <a href="{{ route('login') }}" class="rounded-xl px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100 transition min-h-[44px] inline-flex items-center justify-center">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2 text-sm font-bold text-white shadow-md shadow-emerald-600/25 hover:from-emerald-700 hover:to-teal-700 transition min-h-[44px] inline-flex items-center justify-center active:scale-[0.98]">
                            Daftar
                        </a>
                    </div>
                @else
                    <div class="relative hidden sm:block" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-2.5 rounded-full border border-slate-200/90 bg-white py-1.5 pl-2 pr-3.5 text-left text-sm hover:border-slate-300 shadow-soft-xs transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 min-h-[44px]">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-tr from-emerald-600 to-teal-500 font-bold text-xs text-white shadow-xs">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="hidden sm:block">
                                <p class="text-xs font-bold leading-tight text-ink-900">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] uppercase font-bold text-emerald-600 tracking-wider">{{ Auth::user()->role }}</p>
                            </div>
                            <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute right-0 mt-2 w-60 rounded-2xl border border-slate-200/90 bg-white py-2 shadow-soft-xl z-50">
                            
                            <div class="border-b border-slate-100 px-4 py-2.5 text-xs">
                                <p class="font-bold text-ink-900">{{ Auth::user()->name }}</p>
                                <p class="text-slate-500 truncate text-[11px] mt-0.5">{{ Auth::user()->email }}</p>
                                <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200/80">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    {{ strtoupper(Auth::user()->role) }}
                                </span>
                            </div>

                            <div class="py-1">
                                @if(Auth::user()->isPenyewa())
                                    <a href="{{ route('my.bookings') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-emerald-700 transition">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Booking Saya</span>
                                    </a>
                                @endif

                                @if(Auth::user()->isPengelola() || Auth::user()->isAdmin())
                                    <a href="{{ route('pengelola.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-emerald-700 transition">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                                        </svg>
                                        <span>Dashboard Pengelola</span>
                                    </a>
                                @endif

                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-indigo-700 transition">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                        <span>Admin Platform</span>
                                    </a>
                                @endif

                                <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-emerald-700 transition">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>Pengaturan Profil</span>
                                </a>
                            </div>

                            <div class="border-t border-slate-100 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2.5 px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 transition">
                                        <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        <span>Keluar (Logout)</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endguest

                <!-- Mobile Hamburger Button -->
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden flex items-center justify-center h-11 w-11 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 shadow-soft-xs"
                    aria-label="Buka Menu Navigasi">
                    <svg x-show="!mobileMenuOpen" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer Navigation (Alpine.js) -->
        <div x-show="mobileMenuOpen" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="md:hidden border-t border-slate-200/80 bg-white/95 backdrop-blur-xl px-4 py-4 space-y-3 shadow-soft-xl">
            
            <nav class="space-y-1">
                <a href="{{ route('home') }}" @click="mobileMenuOpen = false"
                    class="flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-bold transition min-h-[44px] {{ request()->routeIs('home') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-700 hover:bg-slate-50' }}">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Cari Lapangan</span>
                </a>

                @auth
                    @if(Auth::user()->isPenyewa())
                        <a href="{{ route('my.bookings') }}" @click="mobileMenuOpen = false"
                            class="flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-bold transition min-h-[44px] {{ request()->routeIs('my.bookings') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-700 hover:bg-slate-50' }}">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Booking Saya</span>
                        </a>
                    @endif

                    @if(Auth::user()->isPengelola() || Auth::user()->isAdmin())
                        <a href="{{ route('pengelola.dashboard') }}" @click="mobileMenuOpen = false"
                            class="flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-bold transition min-h-[44px] {{ request()->routeIs('pengelola.dashboard') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-700 hover:bg-slate-50' }}">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                            </svg>
                            <span>Dashboard Pengelola</span>
                        </a>
                    @endif

                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" @click="mobileMenuOpen = false"
                            class="flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-bold transition min-h-[44px] {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }}">
                            <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span>Admin Platform</span>
                        </a>
                    @endif

                    <a href="{{ route('profile') }}" @click="mobileMenuOpen = false"
                        class="flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-bold transition min-h-[44px] {{ request()->routeIs('profile') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-700 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Pengaturan Profil</span>
                    </a>
                @endauth

                <a href="{{ route('docs') }}" @click="mobileMenuOpen = false"
                    class="flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-bold transition min-h-[44px] {{ request()->routeIs('docs*') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span class="rounded-md bg-ink-900 text-[10px] font-bold text-white px-1.5 py-0.5 font-mono">API</span>
                    <span>Dokumentasi API</span>
                </a>
            </nav>

            <div class="border-t border-slate-200/80 pt-3">
                @guest
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}" class="flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition min-h-[44px]">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="flex items-center justify-center rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-600/20 hover:from-emerald-700 hover:to-teal-700 transition min-h-[44px]">
                            Daftar
                        </a>
                    </div>
                @else
                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200/80">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-600 font-bold text-xs text-white">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-ink-900">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] text-slate-500">{{ Auth::user()->email }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-rose-50 border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-100 transition min-h-[36px]">
                                Keluar
                            </button>
                        </form>
                    </div>
                @endguest
            </div>
        </div>
    </header>

    <!-- Global Alert / Flash Messages -->
    <div class="mx-auto w-full max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
                class="mb-4 flex items-center justify-between rounded-2xl border border-emerald-200/90 bg-emerald-50/90 p-4 text-sm text-emerald-900 shadow-soft-sm backdrop-blur-sm" role="alert">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-xs">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="font-semibold text-xs sm:text-sm">{{ session('success') }}</span>
                </div>
                <button type="button" @click="show = false" class="rounded-lg p-1.5 text-emerald-700 hover:bg-emerald-100 transition" aria-label="Tutup notifikasi">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
                class="mb-4 flex items-center justify-between rounded-2xl border border-rose-200/90 bg-rose-50/90 p-4 text-sm text-rose-900 shadow-soft-sm backdrop-blur-sm" role="alert">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl bg-rose-500 text-white shadow-xs">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <span class="font-semibold text-xs sm:text-sm">{{ session('error') }}</span>
                </div>
                <button type="button" @click="show = false" class="rounded-lg p-1.5 text-rose-700 hover:bg-rose-100 transition" aria-label="Tutup notifikasi">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        @if(session('warning'))
            <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
                class="mb-4 flex items-center justify-between rounded-2xl border border-amber-200/90 bg-amber-50/90 p-4 text-sm text-amber-900 shadow-soft-sm backdrop-blur-sm" role="alert">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-xs">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="font-semibold text-xs sm:text-sm">{{ session('warning') }}</span>
                </div>
                <button type="button" @click="show = false" class="rounded-lg p-1.5 text-amber-700 hover:bg-amber-100 transition" aria-label="Tutup notifikasi">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer (Premium Luxury Dark Palette) -->
    <footer class="border-t border-slate-200/80 bg-white py-12 mt-20 text-slate-500">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 pb-8 border-b border-slate-100">
                <div class="md:col-span-6 space-y-3">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ asset('images/logo.png') }}" alt="SportVenue" class="h-8 w-auto">
                        <span class="text-base font-black tracking-tight text-ink-900">SportVenue Platform</span>
                    </div>
                    <p class="text-xs text-slate-500 max-w-md leading-relaxed">
                        Layanan Web Service dan Sistem Booking Lapangan Olahraga Real-Time dengan jaminan anti double-booking, pembayaran DP otomatis Midtrans QRIS/VA, dan transparansi jadwal operasional.
                    </p>
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 border border-emerald-200/80">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Laravel 12 · Livewire 3
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-700 border border-slate-200">
                            Midtrans Snap Sandbox
                        </span>
                    </div>
                </div>

                <div class="md:col-span-3 space-y-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-ink-900">Navigasi Cepat</h4>
                    <ul class="space-y-1.5 text-xs">
                        <li><a href="{{ route('home') }}" class="hover:text-emerald-600 transition">Cari & Sewa Lapangan</a></li>
                        <li><a href="{{ route('docs') }}" class="hover:text-emerald-600 transition">Spesifikasi REST API</a></li>
                        <li><a href="{{ route('docs.postman') }}" class="hover:text-emerald-600 transition">Download Postman Collection</a></li>
                        <li><a href="{{ route('docs.openapi') }}" target="_blank" class="hover:text-emerald-600 transition">OpenAPI Spec YAML</a></li>
                    </ul>
                </div>

                <div class="md:col-span-3 space-y-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-ink-900">Akademik & Tim</h4>
                    <p class="text-xs text-slate-600 font-medium">Mata Kuliah: Pemrograman Web Servis</p>
                    <p class="text-xs text-slate-500">Kelompok 3 · Teknik Informatika<br>Universitas Harkat Negeri · 2026</p>
                    <div class="text-[11px] text-slate-400 leading-relaxed pt-1">
                        Djafar (23215028) · Arif (23215043)<br>Lutfi (23215046) · Kharis (23215059)
                    </div>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
                <p>&copy; 2026 SportVenue — Hak Cipta Dilindungi Undang-Undang.</p>
                <p class="text-[11px]">Designed with Premium Emerald & Champagne Tokens.</p>
            </div>
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>
</html>
