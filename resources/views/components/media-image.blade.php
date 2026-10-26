{{--
    One way to render a Media Library image everywhere on the public site.
    - Falls back to <x-image-placeholder> when the collection is empty, so a
      missing upload never produces a broken or empty <img>.
    - Falls back to the original file when the requested conversion hasn't
      been generated yet (e.g. media uploaded before a conversion existed).
    - Adds a WebP srcset (800w / 1600w) when those renditions exist.
    - Always sets width/height (layout-shift safe), decoding="async", and
      loading="lazy" unless the image is above the fold ($eager).
    Classes passed as attributes apply to both the <img> and the placeholder;
    $imgClass adds img-only classes (e.g. hover zoom).
--}}
@props([
    'model' => null,
    'collection' => 'cover',
    'conversion' => 'medium',
    'alt' => '',
    'eager' => false,
    'imgClass' => '',
    'placeholderIcon' => true,
    'width' => null,
    'height' => null,
    // How wide the image is displayed; lets the browser pick the right WebP from srcset.
    'sizes' => '(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw',
    // false for small avatars, where the 800w/1600w renditions would be larger downloads.
    'responsive' => true,
])

@php
    $media = $model?->getFirstMedia($collection);
    $src = null;
    $srcset = null;

    if ($media) {
        $src = $conversion && $media->hasGeneratedConversion($conversion) ? $media->getUrl($conversion) : $media->getUrl();

        // Responsive WebP renditions (see HasWebpConversions), when generated.
        $webp = collect(['card' => 800, 'large' => 1600])
            ->filter(fn ($w, $name) => $media->hasGeneratedConversion($name))
            ->map(fn ($w, $name) => $media->getUrl($name).' '.$w.'w');
        $srcset = $responsive && $webp->isNotEmpty() ? $webp->implode(', ') : null;
    }

    $dimensions = ['thumb' => [400, 300], 'medium' => [1200, 700], 'hero' => [1920, 1080]];
    [$defaultWidth, $defaultHeight] = $dimensions[$conversion] ?? [800, 600];
@endphp

@if ($src)
    <img
        src="{{ $src }}"
        @if ($srcset) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif
        alt="{{ $alt }}"
        width="{{ $width ?? $defaultWidth }}"
        height="{{ $height ?? $defaultHeight }}"
        decoding="async"
        @if ($eager) loading="eager" fetchpriority="high" @else loading="lazy" @endif
        {{ $attributes->class(['object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04]', $imgClass]) }}
    >
@else
    <x-image-placeholder :icon="$placeholderIcon" {{ $attributes }} />
@endif
