{{--
    Najdi saw-tooth frieze (the triangle band on Najdi mud-brick walls), as
    inline SVG. Symmetric, so it reads the same in RTL. Decorative only.
    - variant="divider": full-width band between sections
    - variant="underline": short band under a heading
--}}
@props(['variant' => 'divider', 'tone' => 'gold'])

@php
    $color = ['gold' => 'text-gold-600', 'light' => 'text-gold-400', 'sand' => 'text-sand-500'][$tone] ?? 'text-gold-600';
    $id = 'najdi-'.\Illuminate\Support\Str::random(6);
@endphp

@if ($variant === 'underline')
    <svg {{ $attributes->class(['block h-2.5 w-20', $color]) }} viewBox="0 0 80 10" fill="currentColor" aria-hidden="true" focusable="false">
        <path d="M0 10 5 0 10 10Z M14 10 19 0 24 10Z M28 10 33 0 38 10Z M42 10 47 0 52 10Z M56 10 61 0 66 10Z M70 10 75 0 80 10Z"/>
    </svg>
@else
    <div {{ $attributes->class(['flex items-center justify-center gap-3 py-2', $color]) }} aria-hidden="true">
        <svg class="h-3 flex-1 max-w-[18rem]" preserveAspectRatio="none" focusable="false">
            <defs>
                <pattern id="{{ $id }}" width="16" height="12" patternUnits="userSpaceOnUse">
                    <path d="M0 12 8 2 16 12" fill="none" stroke="currentColor" stroke-width="1.5"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#{{ $id }})"/>
        </svg>
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 16 16" fill="currentColor" focusable="false"><path d="M8 0 16 8 8 16 0 8Z M8 4 4 8 8 12 12 8Z" fill-rule="evenodd"/></svg>
        <svg class="h-3 flex-1 max-w-[18rem]" preserveAspectRatio="none" focusable="false"><rect width="100%" height="100%" fill="url(#{{ $id }})"/></svg>
    </div>
@endif
