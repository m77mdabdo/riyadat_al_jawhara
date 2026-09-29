<x-layout :title="$post->title" :description="$post->excerpt">
    @push('head')
        @php
            $articleLd = array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $post->title,
                'description' => $post->excerpt,
                'datePublished' => $post->published_at?->toIso8601String(),
                'dateModified' => $post->updated_at?->toIso8601String(),
                'author' => $post->author ? [
                    '@type' => 'Person',
                    'name' => $post->author->name,
                ] : null,
                'image' => $post->getFirstMediaUrl('cover') ?: null,
                'mainEntityOfPage' => url()->current(),
            ]);
        @endphp
        <script type="application/ld+json">{!! json_encode($articleLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endpush

    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($post->getFirstMediaUrl('cover'))
                <div class="aspect-[21/9] rounded-xl overflow-hidden mb-8">
                    <img src="{{ $post->getFirstMediaUrl('cover', 'medium') }}" alt="{{ $post->title }}" loading="eager" fetchpriority="high" class="h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04]">
                </div>
            @endif

            @if ($post->blogCategory)
                <a href="{{ route('blog.index', ['category' => $post->blogCategory->slug]) }}" class="inline-block text-xs font-semibold px-3 py-1 rounded-full bg-charcoal text-cream mb-4">
                    {{ $post->blogCategory->name }}
                </a>
            @endif

            <h1 class="text-3xl sm:text-4xl font-bold text-charcoal">{{ $post->title }}</h1>

            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-stone-600">
                @if ($post->author)
                    <span>{{ __('site.blog.by') }} <span class="font-semibold text-charcoal">{{ $post->author->name }}</span></span>
                @endif
                @if ($post->published_at)
                    <span><bdi>{{ $post->published_at->format('Y-m-d') }}</bdi></span>
                @endif
                <span class="inline-flex items-center gap-1">
                    <x-heroicon-o-eye class="h-4 w-4" />
                    <bdi>{{ number_format($post->views_count) }}</bdi> {{ __('site.blog.views') }}
                </span>
            </div>

            <div class="prose prose-stone max-w-none mt-8 rtl:leading-loose">
                {!! $post->body !!}
            </div>

            @if ($post->tags->isNotEmpty())
                <div class="mt-8 flex flex-wrap gap-2">
                    @foreach ($post->tags as $tag)
                        <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" class="text-xs font-medium px-3 py-1 rounded-full bg-stone-100 text-stone-700 hover:bg-stone-200 transition-colors">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-charcoal mb-8">{{ __('site.blog.related_articles') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($related as $relatedPost)
                        <a href="{{ route('blog.show', $relatedPost) }}" class="group block rounded-xl overflow-hidden border border-stone-200 bg-white hover:shadow-lg hover:-translate-y-1 transition-all">
                            <div class="aspect-[4/3] bg-stone-200 overflow-hidden">
                                @if ($relatedPost->getFirstMediaUrl('cover'))
                                    <img src="{{ $relatedPost->getFirstMediaUrl('cover', 'medium') }}" alt="" loading="lazy" class="h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04] group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <x-image-placeholder class="h-full w-full" />
                                @endif
                            </div>
                            <div class="p-5">
                                <h3 class="font-semibold text-charcoal">{{ $relatedPost->title }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section id="comments" class="py-16 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ replyTo: null }">
        <h2 class="text-2xl sm:text-3xl font-bold text-charcoal mb-8">
            {{ __('site.blog.comments') }} ({{ $post->approvedComments->count() }})
        </h2>

        @if (session('success'))
            <div class="mb-8 rounded-lg bg-green-50 border border-green-200 text-green-800 px-5 py-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-8 rounded-lg bg-red-50 border border-red-200 text-red-700 px-5 py-4 text-sm">
                <ul class="list-disc ps-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-6">
            @forelse ($post->approvedComments as $comment)
                <x-blog.comment :comment="$comment" :post="$post" />
            @empty
                <p class="text-stone-600">{{ __('site.blog.no_comments_yet') }}</p>
            @endforelse
        </div>

        <div class="mt-10">
            <h3 class="text-lg font-semibold text-charcoal mb-4">{{ __('site.blog.comment_form_title') }}</h3>
            <x-blog.comment-form :post="$post" />
        </div>
    </section>
</x-layout>
