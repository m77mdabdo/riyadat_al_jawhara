{{--
    Mashrabiya-style lattice frame around featured content: a thin lattice
    border (CSS gradients, no images) with gold corners. Symmetric, RTL-safe.
--}}
<div {{ $attributes->class('relative rounded-2xl p-2 mashrabiya-frame') }}>
    <span class="pointer-events-none absolute -top-px -start-px h-5 w-5 rounded-ss-2xl border-s-2 border-t-2 border-gold-600" aria-hidden="true"></span>
    <span class="pointer-events-none absolute -bottom-px -end-px h-5 w-5 rounded-ee-2xl border-e-2 border-b-2 border-gold-600" aria-hidden="true"></span>
    {{ $slot }}
</div>
