{{--
    Subtle Saudi geometric lattice (8-point star grid) for cream sections.
    Absolutely positioned; place inside a `relative` container. Opacity is
    capped at 5% so text contrast is unaffected. Symmetric (RTL-safe).
--}}
@props(['opacity' => '0.05'])

@php $id = 'geo-'.\Illuminate\Support\Str::random(6); @endphp

<svg {{ $attributes->class('pointer-events-none absolute inset-0 h-full w-full text-charcoal') }} style="opacity: {{ min((float) $opacity, 0.05) }}" aria-hidden="true" focusable="false">
    <defs>
        <pattern id="{{ $id }}" width="48" height="48" patternUnits="userSpaceOnUse">
            <path d="M24 4 29 19 44 24 29 29 24 44 19 29 4 24 19 19Z" fill="none" stroke="currentColor" stroke-width="1"/>
            <path d="M0 0 8 8M48 0 40 8M0 48 8 40M48 48 40 40" stroke="currentColor" stroke-width="1"/>
        </pattern>
    </defs>
    <rect width="100%" height="100%" fill="url(#{{ $id }})"/>
</svg>
