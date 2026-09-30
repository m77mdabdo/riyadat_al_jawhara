<x-layout :title="__('site.gallery.title')" :description="__('site.gallery.subtitle')" :breadcrumbs="[['name' => __('site.gallery.title'), 'url' => route('gallery')]]">
    <x-page-header :title="__('site.gallery.title')" :subtitle="__('site.gallery.subtitle')" texture="texture-granite" />

    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @php
            $chip = fn (bool $active) => 'inline-flex min-h-11 items-center rounded-full px-4 py-2 text-sm font-medium transition-colors '.($active ? 'bg-charcoal text-cream' : 'bg-white border border-stone-300 text-stone-700 hover:bg-stone-100');
            $query = fn (array $change) => array_filter(array_merge(['type' => $type, 'stone_category' => $activeCategory], $change));
        @endphp

        <nav class="mb-6 flex flex-wrap gap-2" aria-label="{{ __('site.gallery.filter_type') }}">
            <a href="{{ route('gallery', $query(['type' => null])) }}" class="{{ $chip($type === '') }}" @if ($type === '') aria-current="true" @endif>{{ __('site.common.all') }}</a>
            <a href="{{ route('gallery', $query(['type' => 'projects'])) }}" class="{{ $chip($type === 'projects') }}" @if ($type === 'projects') aria-current="true" @endif>{{ __('site.gallery.projects') }}</a>
            <a href="{{ route('gallery', $query(['type' => 'stones'])) }}" class="{{ $chip($type === 'stones') }}" @if ($type === 'stones') aria-current="true" @endif>{{ __('site.gallery.stones') }}</a>
        </nav>

        @if ($categories->isNotEmpty())
            <nav class="mb-10 flex flex-wrap gap-2" aria-label="{{ __('site.gallery.filter_stone') }}">
                <a href="{{ route('gallery', $query(['stone_category' => null])) }}" class="{{ $chip($activeCategory === '') }}">{{ __('site.gallery.any_stone') }}</a>
                @foreach ($categories as $category)
                    <a href="{{ route('gallery', $query(['stone_category' => $category->slug])) }}" class="{{ $chip($activeCategory === $category->slug) }}" @if ($activeCategory === $category->slug) aria-current="true" @endif>{{ $category->name }}</a>
                @endforeach
            </nav>
        @endif

        @if ($media->isEmpty())
            <p class="py-12 text-center text-stone-600">{{ __('site.gallery.empty') }}</p>
        @else
            <x-lightbox :media="$media->getCollection()" layout="masonry" />
            <div class="mt-12">{{ $media->links('pagination::tailwind') }}</div>
        @endif
    </section>
</x-layout>
