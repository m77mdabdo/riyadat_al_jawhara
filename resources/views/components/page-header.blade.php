{{--
    Branded band at the top of inner pages: stone texture (config/site-images
    slot) + faint geometric pattern + Najdi frieze. The cream wash keeps
    heading contrast >= AA regardless of the texture.
--}}
@props(['title', 'subtitle' => null, 'texture' => 'texture-marble'])

@php $textureUrl = \App\Support\SiteImages::url($texture); @endphp

<section class="relative overflow-hidden bg-sand-100 border-b border-sand-200">
    @if ($textureUrl)
        <img src="{{ $textureUrl }}" alt="" width="1280" height="853" loading="eager" decoding="async" class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-40" aria-hidden="true">
    @endif
    <div class="absolute inset-0 bg-gradient-to-b from-cream/80 via-cream/70 to-sand-100/90" aria-hidden="true"></div>
    <x-motif.geo-pattern />
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-10 sm:pt-16 text-center">
        <h1 class="text-page-title font-bold text-charcoal break-words">{{ $title }}</h1>
        <x-motif.najdi-band variant="underline" class="mx-auto mt-4" />
        @if ($subtitle)
            <p class="mt-4 max-w-2xl mx-auto text-stone-700">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
</section>
