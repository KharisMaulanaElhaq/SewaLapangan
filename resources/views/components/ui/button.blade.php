@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 font-bold tracking-tight rounded-xl transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed select-none min-h-[44px]';

    $sizes = [
        'sm' => 'px-3.5 py-1.5 text-xs min-h-[38px] sm:min-h-[36px]',
        'md' => 'px-4 py-2.5 text-sm min-h-[44px]',
        'lg' => 'px-6 py-3.5 text-base min-h-[48px]',
    ];

    $variants = [
        'primary' => 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-600/25 hover:from-emerald-700 hover:to-teal-700 hover:shadow-lg hover:shadow-emerald-600/30 focus-visible:ring-emerald-500 active:scale-[0.98]',
        'secondary' => 'bg-slate-900 text-white hover:bg-slate-800 shadow-md shadow-slate-900/20 focus-visible:ring-slate-900 active:scale-[0.98]',
        'gold' => 'bg-gradient-to-r from-amber-500 to-yellow-600 text-white shadow-md shadow-amber-500/25 hover:from-amber-600 hover:to-yellow-700 focus-visible:ring-amber-500 active:scale-[0.98]',
        'outline' => 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-300 focus-visible:ring-slate-400 active:scale-[0.98]',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700 shadow-md shadow-rose-600/25 focus-visible:ring-rose-500 active:scale-[0.98]',
        'ghost' => 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 focus-visible:ring-slate-300',
    ];

    $classes = $baseClasses . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
