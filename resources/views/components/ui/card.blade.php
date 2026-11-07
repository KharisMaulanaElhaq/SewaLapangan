@props([
    'hover' => false,
    'padding' => 'md',
])

@php
    $paddings = [
        'none' => '',
        'sm' => 'p-4 sm:p-5',
        'md' => 'p-5 sm:p-6',
        'lg' => 'p-6 sm:p-8',
    ];

    $classes = 'rounded-3xl border border-slate-200/90 bg-white shadow-sm '
        . ($hover ? 'transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-slate-300 ' : '')
        . ($paddings[$padding] ?? $paddings['md']);
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>

