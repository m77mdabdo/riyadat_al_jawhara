@props(['stoneType', 'headingTag' => 'h3'])

<a href="{{ route('stones.show', $stoneType) }}" {{ $attributes->class('group block rounded-xl overflow-hidden border border-stone-200 bg-white hover:shadow-lg hover:-translate-y-1 transition-all') }}>
    <div class="aspect-square bg-stone-200 overflow-hidden">
        <x-media-image :model="$stoneType" conversion="thumb" :alt="$stoneType->name" class="h-full w-full" img-class="group-hover:scale-105 transition-transform duration-500" />
    </div>
    <div class="p-4">
        <{{ $headingTag }} class="font-semibold text-charcoal text-sm break-words">{{ $stoneType->name }}</{{ $headingTag }}>
    </div>
</a>
