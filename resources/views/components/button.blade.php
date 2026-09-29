@props(['href' => null, 'size' => 'md', 'variant' => 'primary'])

@php
    $sizes = [
        'sm' => 'px-5 py-2.5 text-sm',
        'md' => 'px-7 py-3 text-sm',
    ];

    $variants = [
        'primary' => 'bg-charcoal text-cream hover:bg-charcoal-700',
        'inverted' => 'bg-cream text-charcoal hover:bg-stone-100',
        'outline' => 'border border-charcoal text-charcoal hover:bg-charcoal hover:text-cream',
        'outline-light' => 'border border-cream/70 text-cream hover:bg-cream hover:text-charcoal',
    ];

    $classes = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-md font-semibold transition-colors '
        .$sizes[$size].' '.$variants[$variant];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
