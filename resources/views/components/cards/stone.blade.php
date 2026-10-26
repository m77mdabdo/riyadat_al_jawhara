@props(['stoneType', 'headingTag' => 'h3'])

<a href="{{ route('stones.show', $stoneType) }}" {{ $attributes->class('group block rounded-xl overflow-hidden border border-stone-200 bg-white hover:border-sand-300 hover:shadow-lg hover:-translate-y-1 transition-all duration-300') }}>
    <div class="aspect-square bg-stone-200 overflow-hidden">
        <x-media-image :model="$stoneType" conversion="thumb" :alt="$stoneType->name" sizes="(min-width: 1024px) 25vw, (min-width: 640px) 33vw, 50vw" class="h-full w-full" img-class="group-hover:scale-105 transition-transform duration-700" />
    </div>
    <div class="p-4">
        <{{ $headingTag }} class="font-semibold text-charcoal text-sm break-words"><span class="link-sweep">{{ $stoneType->name }}</span></{{ $headingTag }}>
    </div>
</a>
