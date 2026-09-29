{{-- Section heading with the Najdi underline; one definition for every section. --}}
@props(['title', 'subtitle' => null, 'align' => 'center', 'tag' => 'h2', 'tone' => 'dark'])

<div {{ $attributes->class([
    'max-w-2xl',
    'mx-auto text-center' => $align === 'center',
]) }}>
    <{{ $tag }} @class(['text-section-title font-bold break-words', 'text-charcoal' => $tone === 'dark', 'text-cream' => $tone === 'light'])>{{ $title }}</{{ $tag }}>
    <x-motif.najdi-band variant="underline" :tone="$tone === 'light' ? 'light' : 'gold'" @class(['mt-3', 'mx-auto' => $align === 'center']) />
    @if ($subtitle)
        <p @class(['mt-4', 'text-stone-600' => $tone === 'dark', 'text-stone-300' => $tone === 'light'])>{{ $subtitle }}</p>
    @endif
</div>
