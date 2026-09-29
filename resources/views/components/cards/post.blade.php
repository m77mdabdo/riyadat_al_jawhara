@props(['post', 'headingTag' => 'h2', 'compact' => false])

<a href="{{ route('blog.show', $post) }}" {{ $attributes->class('group block rounded-xl overflow-hidden border border-stone-200 bg-white hover:border-sand-300 hover:shadow-lg hover:-translate-y-1 transition-all duration-300') }}>
    <div class="aspect-[4/3] bg-stone-200 overflow-hidden relative">
        <x-media-image :model="$post" :alt="$post->title" class="h-full w-full" img-class="group-hover:scale-105 transition-transform duration-700" />
        @if (! $compact && $post->blogCategory)
            <span class="absolute top-3 start-3 bg-charcoal text-cream text-sm sm:text-xs font-semibold px-3 py-1 rounded-full">{{ $post->blogCategory->name }}</span>
        @endif
    </div>
    <div class="p-5">
        @if (! $compact && $post->published_at)
            <p class="text-sm sm:text-xs text-stone-600 mb-2"><time datetime="{{ $post->published_at->toDateString() }}"><bdi>{{ $post->published_at->format('Y-m-d') }}</bdi></time></p>
        @endif
        <{{ $headingTag }} class="font-semibold text-charcoal break-words"><span class="link-sweep">{{ $post->title }}</span></{{ $headingTag }}>
        @if (! $compact && $post->excerpt)
            <p class="text-sm text-stone-600 mt-2 line-clamp-2">{{ $post->excerpt }}</p>
        @endif
        @unless ($compact)
            <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-charcoal group-hover:underline">
                {{ __('site.blog.read_article') }} <x-heroicon-o-arrow-right class="h-4 w-4 rtl:rotate-180" aria-hidden="true" />
            </span>
        @endunless
    </div>
</a>
