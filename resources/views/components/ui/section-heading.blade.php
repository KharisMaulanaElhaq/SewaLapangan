@props([
    'badge' => null,
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-slate-200/80 pb-5']) }}>
    <div>
        @if($badge)
            <div class="mb-2">
                <x-ui.badge variant="emerald" size="sm" :dot="true">{{ $badge }}</x-ui.badge>
            </div>
        @endif
        <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 leading-tight">
            {{ $title }}
        </h2>
        @if($description)
            <p class="text-xs sm:text-sm text-slate-500 mt-1.5 max-w-2xl leading-relaxed">
                {{ $description }}
            </p>
        @endif
    </div>

    @if(isset($action))
        <div class="flex items-center gap-2 self-start sm:self-auto flex-shrink-0">
            {{ $action }}
        </div>
    @endif
</div>
