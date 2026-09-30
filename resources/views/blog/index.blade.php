<x-layout :title="__('site.blog.title')" :description="__('site.blog.subtitle')" :robots="$search !== '' ? 'noindex, follow' : null" :breadcrumbs="[['name' => __('site.blog.title'), 'url' => route('blog.index')]]">
    <x-page-header :title="__('site.blog.title')" :subtitle="__('site.blog.subtitle')" texture="texture-marble" />

    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="GET" role="search" class="flex flex-wrap gap-3 sm:gap-4 mb-10">
            <input
                type="search"
                name="q"
                aria-label="{{ __('site.blog.search_placeholder') }}"
                value="{{ $search }}"
                placeholder="{{ __('site.blog.search_placeholder') }}"
                class="w-full sm:w-auto sm:flex-1 sm:min-w-[200px] min-h-11 rounded-md border-stone-300 px-3 py-2.5 text-base sm:text-sm text-charcoal focus:border-charcoal focus:ring-charcoal"
            >
            <x-filter-select name="category" :label="__('site.blog.category')" :options="$categories->pluck('name', 'slug')" :selected="$activeCategory" />
            <x-filter-select name="tag" :label="__('site.blog.tag')" :options="$tags->pluck('name', 'slug')" :selected="$activeTag" />
            <button type="submit" class="w-full sm:w-auto min-h-11 rounded-md bg-charcoal text-cream px-5 py-2.5 text-sm font-semibold hover:bg-charcoal-700 transition-colors">
                {{ __('site.common.filter') }}
            </button>
            @if ($activeCategory || $activeTag || $search)
                <a href="{{ route('blog.index') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-stone-600 hover:text-charcoal">{{ __('site.common.all') }} &times;</a>
            @endif
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($posts as $post)
                <x-cards.post :post="$post" />
            @empty
                <p class="text-stone-600 col-span-full text-center py-12">{{ __('site.blog.no_posts_yet') }}</p>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $posts->links('pagination::tailwind') }}
        </div>
    </section>
</x-layout>
