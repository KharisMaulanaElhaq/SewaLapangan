@props([
    'title' => 'Tidak ada data ditemukan',
    'description' => 'Coba sesuaikan filter pencarian atau parameter Anda.',
    'actionText' => null,
    'actionHref' => null,
])

<div {{ $attributes->merge(['class' => 'my-8 rounded-3xl border border-dashed border-slate-300 bg-white/60 backdrop-blur-sm p-8 sm:p-12 text-center']) }}>
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
        {{ $icon ?? '' }}
        @if(!isset($icon))
            <svg class="h-7 w-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
        @endif
    </div>

    <h3 class="mt-4 text-base font-bold text-slate-900 tracking-tight">{{ $title }}</h3>
    <p class="mt-1.5 text-xs sm:text-sm text-slate-500 max-w-md mx-auto leading-relaxed">{{ $description }}</p>

    @if($actionText && $actionHref)
        <div class="mt-6">
            <x-ui.button :href="$actionHref" variant="primary" size="sm">
                {{ $actionText }}
            </x-ui.button>
        </div>
    @endif

    {{ $slot }}
</div>
