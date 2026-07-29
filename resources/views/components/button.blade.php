@props(['href' => null, 'size' => 'md', 'variant' => 'primary'])

@php
    $sizes = [
        'sm' => 'px-5 py-2.5 text-sm',
        'md' => 'px-7 py-3 text-sm',
    ];

    $variants = [
        'primary' => 'bg-charcoal text-cream hover:bg-charcoal-700',
        'inverted' => 'bg-cream text-charcoal hover:bg-stone-100',
    ];

    $classes = 'inline-flex items-center justify-center gap-2 rounded-md font-semibold transition-colors '
        .$sizes[$size].' '.$variants[$variant];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
