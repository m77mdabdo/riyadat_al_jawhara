{{-- Extra content blocks of a page (Website Content → Pages); prepared and sanitized by App\Support\PageBlocks. --}}
@props(['page'])

@foreach (\App\Support\PageBlocks::for($page) as $block)
    @switch($block['type'])
        @case('rich_text')
            <section class="py-12 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="prose prose-stone max-w-none rtl:leading-prose">{!! $block['html'] !!}</div>
            </section>
            @break

        @case('image_text')
            <section class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                    @if ($block['image'])
                        <div @class(['overflow-hidden rounded-xl bg-sand-100 aspect-[4/3]', 'lg:order-2' => $block['image_end']])>
                            <img src="{{ $block['image'] }}" alt="{{ $block['alt'] }}" width="1200" height="900" loading="lazy" decoding="async" class="h-full w-full object-cover">
                        </div>
                    @endif
                    <div @class(['lg:col-span-2' => ! $block['image']])>
                        @if ($block['title'])
                            <h2 class="text-section-title font-bold text-charcoal">{{ $block['title'] }}</h2>
                        @endif
                        <div class="mt-4 prose prose-stone max-w-none rtl:leading-prose">{!! $block['html'] !!}</div>
                    </div>
                </div>
            </section>
            @break

        @case('gallery')
            <section class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if ($block['title'])
                    <h2 class="mb-8 text-section-title font-bold text-charcoal text-center">{{ $block['title'] }}</h2>
                @endif
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($block['images'] as $image)
                        <div class="overflow-hidden rounded-xl bg-sand-100 aspect-[4/3]">
                            <img src="{{ $image }}" alt="" width="800" height="600" loading="lazy" decoding="async" class="h-full w-full object-cover">
                        </div>
                    @endforeach
                </div>
            </section>
            @break

        @case('cta')
            <section class="bg-sand-100 py-16">
                <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <x-section-heading :title="$block['title']" :subtitle="$block['text'] ?: null" />
                    <div class="mt-8">
                        <x-button :href="$block['url']">{{ $block['label'] }}</x-button>
                    </div>
                </div>
            </section>
            @break

        @case('quote')
            <section class="py-12 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <figure class="border-s-4 border-gold-400 ps-6">
                    <blockquote class="text-xl text-charcoal leading-prose">&ldquo;{{ $block['quote'] }}&rdquo;</blockquote>
                    @if ($block['author'])
                        <figcaption class="mt-3 text-sm text-stone-600">— {{ $block['author'] }}</figcaption>
                    @endif
                </figure>
            </section>
            @break
    @endswitch
@endforeach
