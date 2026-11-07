@props([
    'variant' => 'emerald',
    'size' => 'md',
    'dot' => false,
])

@php
    $baseClasses = 'inline-flex items-center gap-1.5 font-bold uppercase tracking-wider rounded-full border transition-colors';

    $sizes = [
        'sm' => 'px-2 py-0.5 text-[10px]',
        'md' => 'px-2.5 py-1 text-xs',
        'lg' => 'px-3 py-1.5 text-xs',
    ];

    $variants = [
        'emerald' => 'bg-emerald-50 text-emerald-800 border-emerald-200/80',
        'gold' => 'bg-amber-50 text-amber-900 border-amber-300/80',
        'blue' => 'bg-blue-50 text-blue-800 border-blue-200/80',
        'rose' => 'bg-rose-50 text-rose-800 border-rose-200/80',
        'slate' => 'bg-slate-100 text-slate-700 border-slate-200',
        'dark' => 'bg-slate-900 text-white border-slate-800',
    ];

    $dotColors = [
        'emerald' => 'bg-emerald-500',
        'gold' => 'bg-amber-500',
        'blue' => 'bg-blue-500',
        'rose' => 'bg-rose-500',
        'slate' => 'bg-slate-400',
        'dark' => 'bg-emerald-400',
    ];

    $classes = $baseClasses . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['emerald']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="h-1.5 w-1.5 rounded-full {{ $dotColors[$variant] ?? 'bg-emerald-500' }}"></span>
    @endif
    {{ $slot }}
</span>

