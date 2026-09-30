<x-layout :title="$post->seoTitle($post->title)" :description="$post->seoDescription($post->excerpt ?: $post->body)" :image="$post->coverImageUrl()" type="article" :published-at="$post->published_at" :modified-at="$post->updated_at" :breadcrumbs="[['name' => __('site.blog.title'), 'url' => route('blog.index')], ['name' => $post->title, 'url' => route('blog.show', $post)]]">
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
                'image' => $post->coverImageUrl(),
                'mainEntityOfPage' => url()->current(),
            ]);
        @endphp
        <script type="application/ld+json">{!! json_encode($articleLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endpush

    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($post->getFirstMedia('cover'))
                <div class="aspect-[16/9] sm:aspect-[21/9] rounded-xl overflow-hidden mb-8">
                    <x-media-image :model="$post" :alt="$post->title" :eager="true" sizes="(min-width: 768px) 720px, 100vw" class="h-full w-full" />
                </div>
            @endif

            @if ($post->blogCategory)
                <a href="{{ route('blog.index', ['category' => $post->blogCategory->slug]) }}" class="inline-flex min-h-11 sm:min-h-0 items-center text-sm sm:text-xs font-semibold px-4 sm:px-3 py-1 rounded-full bg-charcoal text-cream mb-4">
                    {{ $post->blogCategory->name }}
                </a>
            @endif

            <h1 class="text-page-title font-bold text-charcoal break-words">{{ $post->title }}</h1>

            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-stone-600">
                @if ($post->author)
                    <span>{{ __('site.blog.by') }} <span class="font-semibold text-charcoal">{{ $post->author->name }}</span></span>
                @endif
                @if ($post->published_at)
                    <span><time datetime="{{ $post->published_at->toDateString() }}"><bdi>{{ $post->published_at->format('Y-m-d') }}</bdi></time></span>
                @endif
                <span class="inline-flex items-center gap-1">
                    <x-heroicon-o-eye class="h-4 w-4" />
                    <bdi>{{ number_format($post->views_count) }}</bdi> {{ __('site.blog.views') }}
                </span>
            </div>

            <div class="prose prose-stone max-w-none mt-8 rtl:leading-loose">
                {!! str($post->body)->sanitizeHtml() !!}
            </div>

            @if ($post->tags->isNotEmpty())
                <div class="mt-8 flex flex-wrap gap-2">
                    @foreach ($post->tags as $tag)
                        <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" class="inline-flex min-h-11 sm:min-h-0 items-center text-sm sm:text-xs font-medium px-4 sm:px-3 py-1 rounded-full bg-stone-100 text-stone-700 hover:bg-stone-200 transition-colors">
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
                <h2 class="text-section-title font-bold text-charcoal mb-8">{{ __('site.blog.related_articles') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($related as $relatedPost)
                        <x-cards.post :post="$relatedPost" heading-tag="h3" :compact="true" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section id="comments" class="py-16 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-24" x-data="{ replyTo: @js($errors->any() && old('parent_id') ? (int) old('parent_id') : null) }">
        <h2 class="text-section-title font-bold text-charcoal mb-8">
            {{ __('site.blog.comments') }} ({{ $post->approvedComments->count() }})
        </h2>

        @if (session('success'))
            <div role="status" class="mb-8 rounded-lg bg-green-50 border border-green-200 text-green-800 px-5 py-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="mb-8 rounded-lg bg-red-50 border border-red-200 text-red-700 px-5 py-4 text-sm">
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
