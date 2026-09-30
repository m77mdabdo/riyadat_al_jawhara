<x-layout>
    @php
        // Slides that have an uploaded image use it as the hero background;
        // otherwise the default poster (and, on wider screens, the video) shows.
        $slideImages = $sliders->values()->map(fn ($slider) => $slider->heroImageUrl())->filter();
        $hasSlideImages = $slideImages->isNotEmpty();
        // The old marble video only plays when there is no slide image and no
        // curated hero photo.
        $useVideo = ! $hasSlideImages && ! \App\Support\SiteImages::exists('hero');
        $firstSlideImage = $slideImages->get(0);
        // Hero background when a slide has no image of its own: the curated
        // stock hero (config/site-images.php), else the original poster.
        $heroFallback = \App\Support\SiteImages::url('hero', asset('images/hero-poster.webp'));
        $heroSrcset = \App\Support\SiteImages::srcset('hero');
    @endphp

    @push('head')
        {{-- LCP image: responsive preload so phones fetch the 640/1024 rendition. --}}
        @if ($firstSlideImage || ! $heroSrcset)
            <link rel="preload" as="image" href="{{ $firstSlideImage ?? $heroFallback }}" fetchpriority="high">
        @else
            <link rel="preload" as="image" href="{{ $heroFallback }}" imagesrcset="{{ $heroSrcset }}" imagesizes="100vw" fetchpriority="high">
        @endif
    @endpush

    {{-- Hero --}}
    <section class="relative bg-charcoal text-cream overflow-hidden min-h-hero flex flex-col"
        aria-roledescription="carousel"
        aria-label="{{ __('site.common.slideshow') }}"
        x-data="{
            active: 0,
            count: {{ max($sliders->count(), 1) }},
            playing: false,
            timer: null,
            mx: 50,
            my: 50,
            cursorFx: false,
            init() {
                this.cursorFx = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
                // No autoplay for a single slide or for visitors who prefer reduced motion.
                if (this.count > 1 && ! window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    this.play();
                }
            },
            play() {
                clearInterval(this.timer);
                this.playing = true;
                this.timer = setInterval(() => this.active = (this.active + 1) % this.count, 6000);
            },
            stop() {
                clearInterval(this.timer);
                this.playing = false;
            },
            go(index) {
                this.active = index;
                if (this.playing) this.play();
            },
        }"
        @mousemove="if (cursorFx) { const r = $el.getBoundingClientRect(); mx = ((event.clientX - r.left) / r.width) * 100; my = ((event.clientY - r.top) / r.height) * 100; }"
    >
        <div class="absolute inset-0" x-data="{ showVideo: false }" x-init="showVideo = {{ $useVideo ? "window.matchMedia('(min-width: 640px)').matches" : 'false' }}">
            <img src="{{ $heroFallback }}" @if ($heroSrcset) srcset="{{ $heroSrcset }}" sizes="100vw" @endif alt="" width="1920" height="1280" decoding="async" class="absolute inset-0 h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04]" @if ($firstSlideImage) loading="lazy" @else loading="eager" fetchpriority="high" @endif>
            @if ($useVideo)
                <template x-if="showVideo">
                    <video autoplay muted loop playsinline class="absolute inset-0 h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04]">
                        <source src="{{ asset('videos/hero-background.mp4') }}" type="video/mp4">
                    </video>
                </template>
            @endif
            @foreach ($slideImages as $index => $imageUrl)
                <img src="{{ $imageUrl }}" alt="" width="1920" height="1080" decoding="async" x-show="active === {{ $index }}" x-transition:enter="transition-opacity duration-700" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" @if ($index === 0) fetchpriority="high" @else x-cloak loading="lazy" @endif class="absolute inset-0 h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04]">
            @endforeach
            <div class="absolute inset-0 bg-gradient-to-t from-charcoal/90 to-charcoal/40"></div>
        </div>

        <div x-show="cursorFx" x-cloak class="absolute inset-0" :style="`background: radial-gradient(500px circle at ${mx}% ${my}%, rgba(248,246,243,0.10), transparent 60%)`"></div>

        <div class="relative flex-1 flex items-center">
            <div class="w-full">
                @forelse ($sliders->values() as $index => $slider)
                    <div x-show="active === {{ $index }}" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" @if ($index > 0) x-cloak @endif role="group" aria-roledescription="slide" aria-label="{{ $index + 1 }} / {{ $sliders->count() }}" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 text-center">
                        {{-- One h1 per page: later slides use an h2 with the same styling. --}}
                        <{{ $index === 0 ? 'h1' : 'h2' }} class="text-hero font-extrabold ltr:tracking-tight break-words">{{ $slider->title }}</{{ $index === 0 ? 'h1' : 'h2' }}>
                        @if ($slider->subtitle)
                            <p class="mt-5 text-base sm:text-lg text-stone-300 max-w-2xl mx-auto">{{ $slider->subtitle }}</p>
                        @endif
                        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                            @if ($slider->button_text && $slider->button_link)
                                <x-button :href="\App\Support\LocalizedUrl::link($slider->button_link)" variant="inverted">{{ $slider->button_text }}</x-button>
                            @else
                                <x-button :href="route('contact')" variant="inverted">{{ __('site.common.request_quote') }}</x-button>
                            @endif
                            @if ($settings->whatsapp_url)
                                <x-button :href="$settings->whatsapp_url" variant="outline-light" data-wa-track target="_blank" rel="noopener noreferrer">
                                    <x-social-icon network="whatsapp" class="h-4 w-4" /> {{ __('site.common.whatsapp_us') }}
                                </x-button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 text-center">
                        <h1 class="text-hero font-extrabold ltr:tracking-tight break-words">{{ __('site.meta.site_name') }}</h1>
                        <p class="mt-5 text-base sm:text-lg text-stone-300">{{ __('site.meta.tagline') }}</p>
                        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                            <x-button :href="route('contact')" variant="inverted">{{ __('site.common.request_quote') }}</x-button>
                            @if ($settings->whatsapp_url)
                                <x-button :href="$settings->whatsapp_url" variant="outline-light" data-wa-track target="_blank" rel="noopener noreferrer">
                                    <x-social-icon network="whatsapp" class="h-4 w-4" /> {{ __('site.common.whatsapp_us') }}
                                </x-button>
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        @if ($sliders->count() > 1)
            <div class="relative flex items-center justify-center gap-1 pb-6">
                @foreach ($sliders->values() as $index => $slider)
                    <button type="button" @click="go({{ $index }})" class="group h-11 w-11 flex items-center justify-center rounded-full focus-visible:ring-2 focus-visible:ring-cream focus-visible:ring-offset-2 focus-visible:ring-offset-charcoal" :aria-current="active === {{ $index }} ? 'true' : 'false'" aria-label="{{ __('site.common.go_to_slide', ['number' => $index + 1]) }}">
                        <span class="h-2.5 w-2.5 rounded-full transition-colors" :class="active === {{ $index }} ? 'bg-cream' : 'bg-stone-500 group-hover:bg-stone-300'"></span>
                    </button>
                @endforeach
                <button type="button" @click="playing ? stop() : play()" class="ms-2 h-11 w-11 flex items-center justify-center rounded-full text-cream hover:bg-white/10 focus-visible:ring-2 focus-visible:ring-cream focus-visible:ring-offset-2 focus-visible:ring-offset-charcoal" :aria-label="playing ? @js(__('site.common.pause_slideshow')) : @js(__('site.common.play_slideshow'))">
                    <svg x-show="playing" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h3v14H7zM14 5h3v14h-3z"/></svg>
                    <svg x-show="! playing" x-cloak class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                </button>
            </div>
        @endif
    </section>

    {!! $sections !!}
</x-layout>
