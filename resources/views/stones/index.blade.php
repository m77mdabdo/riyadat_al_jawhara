<x-layout :title="__('site.stones.title')">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-charcoal">{{ __('site.stones.title') }}</h1>
        </div>
    </section>

    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap gap-2 mb-10">
            <a href="{{ route('stones.index') }}" class="px-4 py-2 rounded-full text-sm font-medium {{ $activeCategory === '' ? 'bg-charcoal text-cream' : 'bg-white border border-stone-300 text-stone-700 hover:bg-stone-100' }}">
                {{ __('site.common.all') }}
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('stones.index', ['stone_category' => $category->slug]) }}" class="px-4 py-2 rounded-full text-sm font-medium {{ $activeCategory === $category->slug ? 'bg-charcoal text-cream' : 'bg-white border border-stone-300 text-stone-700 hover:bg-stone-100' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse ($stoneTypes as $stoneType)
                <a href="{{ route('stones.show', $stoneType) }}" class="group block rounded-xl overflow-hidden border border-stone-200 bg-white">
                    <div class="aspect-square bg-stone-200 overflow-hidden">
                        @if ($stoneType->getFirstMediaUrl('cover'))
                            <img src="{{ $stoneType->getFirstMediaUrl('cover', 'thumb') }}" alt="{{ $stoneType->name }}" loading="lazy" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-charcoal text-sm">{{ $stoneType->name }}</h3>
                        @if ($stoneType->origin)
                            <p class="text-xs text-stone-500 mt-1">{{ $stoneType->origin }}</p>
                        @endif
                    </div>
                </a>
            @empty
                <p class="text-stone-600 col-span-full text-center">{{ __('site.common.no_results') }}</p>
            @endforelse
        </div>
    </section>
</x-layout>
