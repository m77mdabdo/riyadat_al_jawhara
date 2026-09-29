{{--
    One way to render a Media Library image everywhere on the public site.
    - Falls back to <x-image-placeholder> when the collection is empty, so a
      missing upload never produces a broken or empty <img>.
    - Falls back to the original file when the requested conversion hasn't
      been generated yet (e.g. media uploaded before a conversion existed).
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
])

@php
    $media = $model?->getFirstMedia($collection);
    $src = null;

    if ($media) {
        $src = $conversion && $media->hasGeneratedConversion($conversion) ? $media->getUrl($conversion) : $media->getUrl();
    }

    $sizes = ['thumb' => [400, 300], 'medium' => [1200, 700], 'hero' => [1920, 1080]];
    [$defaultWidth, $defaultHeight] = $sizes[$conversion] ?? [800, 600];
@endphp

@if ($src)
    <img
        src="{{ $src }}"
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
