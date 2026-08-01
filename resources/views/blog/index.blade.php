<x-layout :title="__('site.blog.title')" :description="__('site.blog.subtitle')">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-charcoal">{{ __('site.blog.title') }}</h1>
            <p class="mt-3 text-stone-600">{{ __('site.blog.subtitle') }}</p>
        </div>
    </section>

    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="GET" class="flex flex-wrap gap-4 mb-10">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="{{ __('site.blog.search_placeholder') }}"
                class="flex-1 min-w-[200px] rounded-md border-stone-300 px-3 py-2.5 text-sm text-charcoal focus:border-charcoal focus:ring-charcoal"
            >
            <select name="category" onchange="this.form.submit()" aria-label="{{ __('site.blog.category') }}" class="rounded-md border-stone-300 px-3 py-2.5 text-sm text-charcoal focus:border-charcoal focus:ring-charcoal">
                <option value="">{{ __('site.blog.category') }}: {{ __('site.common.all') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected($activeCategory === $category->slug)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="tag" onchange="this.form.submit()" aria-label="{{ __('site.blog.tag') }}" class="rounded-md border-stone-300 px-3 py-2.5 text-sm text-charcoal focus:border-charcoal focus:ring-charcoal">
                <option value="">{{ __('site.blog.tag') }}: {{ __('site.common.all') }}</option>
                @foreach ($tags as $tag)
                    <option value="{{ $tag->slug }}" @selected($activeTag === $tag->slug)>{{ $tag->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md bg-charcoal text-cream px-5 py-2.5 text-sm font-semibold hover:bg-charcoal-700 transition-colors">
                {{ __('site.common.filter') }}
            </button>
            @if ($activeCategory || $activeTag || $search)
                <a href="{{ route('blog.index') }}" class="text-sm font-medium text-stone-600 hover:text-charcoal self-center">{{ __('site.common.all') }} &times;</a>
            @endif
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($posts as $post)
                <a href="{{ route('blog.show', $post) }}" class="group block rounded-xl overflow-hidden border border-stone-200 bg-white hover:shadow-lg hover:-translate-y-1 transition-all">
                    <div class="aspect-[4/3] bg-stone-200 overflow-hidden relative">
                        @if ($post->getFirstMediaUrl('cover'))
                            <img src="{{ $post->getFirstMediaUrl('cover', 'medium') }}" alt="" loading="lazy" class="h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04] group-hover:scale-105 transition-transform duration-500">
                        @else
                            <x-image-placeholder class="h-full w-full" />
                        @endif
                        @if ($post->blogCategory)
                            <span class="absolute top-3 start-3 bg-charcoal text-cream text-xs font-semibold px-3 py-1 rounded-full">{{ $post->blogCategory->name }}</span>
                        @endif
                    </div>
                    <div class="p-5">
                        <p class="text-xs text-stone-600 mb-2"><bdi>{{ $post->published_at->format('Y-m-d') }}</bdi></p>
                        <h2 class="font-semibold text-charcoal">{{ $post->title }}</h2>
                        @if ($post->excerpt)
                            <p class="text-sm text-stone-600 mt-2 line-clamp-2">{{ $post->excerpt }}</p>
                        @endif
                        <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-charcoal group-hover:underline">
                            {{ __('site.blog.read_article') }} <x-heroicon-o-arrow-right class="h-4 w-4 rtl:rotate-180" />
                        </span>
                    </div>
                </a>
            @empty
                <p class="text-stone-600 col-span-full text-center py-12">{{ __('site.blog.no_posts_yet') }}</p>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $posts->links() }}
        </div>
    </section>
</x-layout>
