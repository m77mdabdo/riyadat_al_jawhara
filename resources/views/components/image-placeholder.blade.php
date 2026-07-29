@props(['icon' => true])

<div {{ $attributes->merge(['class' => 'relative flex items-center justify-center bg-stone-100 overflow-hidden']) }}>
    <svg class="absolute inset-0 h-full w-full text-stone-300" preserveAspectRatio="none" viewBox="0 0 200 200" fill="none" aria-hidden="true">
        <path d="M-10 45 Q40 15 90 45 T210 40" stroke="currentColor" stroke-width="1"/>
        <path d="M-10 95 Q50 65 110 92 T210 75" stroke="currentColor" stroke-width="1"/>
        <path d="M-10 150 Q35 120 95 148 T210 135" stroke="currentColor" stroke-width="1"/>
        <path d="M-10 15 Q60 40 120 12 T210 20" stroke="currentColor" stroke-width="0.75"/>
    </svg>
    @if ($icon)
        <span class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg bg-stone-200">
            <svg class="absolute -top-1.5 start-1/2 -translate-x-1/2 h-3 w-3 text-charcoal" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3 6-3 14-3-14 3-6z"/></svg>
            <span class="font-sans text-charcoal font-extrabold text-sm tracking-wide">JRA</span>
        </span>
    @endif
</div>
