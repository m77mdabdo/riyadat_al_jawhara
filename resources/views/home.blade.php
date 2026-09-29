<x-layout>
    @php
        // Slides that have an uploaded image use it as the hero background;
        // otherwise the default poster (and, on wider screens, the video) shows.
        $slideImages = $sliders->values()->map(fn ($slider) => $slider->heroImageUrl())->filter();
        $hasSlideImages = $slideImages->isNotEmpty();
        $firstSlideImage = $slideImages->get(0);
    @endphp

    @push('head')
        <link rel="preload" as="image" href="{{ $firstSlideImage ?? asset('images/hero-poster.webp') }}" fetchpriority="high">
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
        <div class="absolute inset-0" x-data="{ showVideo: false }" x-init="showVideo = {{ $hasSlideImages ? 'false' : "window.matchMedia('(min-width: 640px)').matches" }}">
            <img src="{{ asset('images/hero-poster.webp') }}" alt="" width="1280" height="720" decoding="async" class="absolute inset-0 h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04]" @if ($firstSlideImage) loading="lazy" @else loading="eager" fetchpriority="high" @endif>
            @unless ($hasSlideImages)
                <template x-if="showVideo">
                    <video autoplay muted loop playsinline class="absolute inset-0 h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04]">
                        <source src="{{ asset('videos/hero-background.mp4') }}" type="video/mp4">
                    </video>
                </template>
            @endunless
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
                        @if ($slider->button_text && $slider->button_link)
                            <x-button :href="$slider->button_link" variant="inverted" class="mt-8">
                                {{ $slider->button_text }}
                            </x-button>
                        @endif
                    </div>
                @empty
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 text-center">
                        <h1 class="text-hero font-extrabold ltr:tracking-tight break-words">{{ __('site.meta.site_name') }}</h1>
                        <p class="mt-5 text-base sm:text-lg text-stone-300">{{ __('site.meta.tagline') }}</p>
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

    {{-- Services --}}
    <section x-reveal class="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-section-title font-bold text-charcoal">{{ __('site.home.our_services') }}</h2>
            <p class="mt-3 text-stone-600">{{ __('site.home.our_services_subtitle') }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($services as $service)
                <x-cards.service :service="$service" />
            @endforeach
        </div>
    </section>

    <x-marble-divider />

    {{-- Featured Projects --}}
    @if ($featuredProjects->isNotEmpty())
        <section class="py-20 bg-white">
            <div x-reveal class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-12">
                    <h2 class="text-section-title font-bold text-charcoal">{{ __('site.home.featured_projects') }}</h2>
                    <a href="{{ route('projects.index') }}" class="inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-charcoal hover:underline">{{ __('site.common.view_all') }} <x-heroicon-o-arrow-right class="h-4 w-4 rtl:rotate-180" aria-hidden="true" /></a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($featuredProjects as $project)
                        <x-cards.project :project="$project" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Stone Collection --}}
    @if ($stoneTypes->isNotEmpty())
        <section x-reveal class="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-12">
                <h2 class="text-section-title font-bold text-charcoal">{{ __('site.home.stone_collection') }}</h2>
                <a href="{{ route('stones.index') }}" class="inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-charcoal hover:underline">{{ __('site.common.view_all') }} <x-heroicon-o-arrow-right class="h-4 w-4 rtl:rotate-180" aria-hidden="true" /></a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($stoneTypes as $stoneType)
                    <x-cards.stone :stone-type="$stoneType" />
                @endforeach
            </div>
        </section>
    @endif

    <x-marble-divider />

    {{-- How We Work --}}
    <section x-reveal class="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-section-title font-bold text-charcoal">{{ __('site.process.title') }}</h2>
            <p class="mt-3 text-stone-600">{{ __('site.process.subtitle') }}</p>
        </div>

        <div class="relative">
            <div class="hidden lg:block absolute top-6 left-0 right-0 h-px bg-stone-300"></div>
            <div class="lg:hidden absolute top-0 bottom-0 start-6 w-px bg-stone-300"></div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-10 lg:gap-6">
                @foreach (__('site.process.steps') as $index => $step)
                    <div class="relative flex items-start lg:flex-col lg:items-center gap-4 lg:gap-0 lg:text-center">
                        <div class="relative z-10 shrink-0 h-12 w-12 rounded-full bg-charcoal text-cream flex items-center justify-center font-bold">
                            {{ $index + 1 }}
                        </div>
                        <div class="lg:mt-5">
                            <h3 class="font-semibold text-charcoal">{{ $step['title'] }}</h3>
                            <p class="mt-1 text-sm text-stone-600 leading-relaxed rtl:leading-loose">{{ $step['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    @if ($testimonials->isNotEmpty())
        <section x-reveal class="relative py-20 bg-charcoal text-cream overflow-hidden">
            <img src="{{ asset('images/textures/stone-texture.webp') }}" alt="" aria-hidden="true" width="1280" height="720" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover opacity-10">
            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-section-title font-bold text-center mb-12">{{ __('site.home.testimonials') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" x-data>
                    @foreach ($testimonials as $testimonial)
                        <div class="rounded-xl bg-white/5 border border-white/10 p-6">
                            <div class="flex gap-1 mb-3 text-stone-300">
                                @for ($i = 0; $i < 5; $i++)
                                    <svg class="h-4 w-4 {{ $i < $testimonial->rating ? 'text-cream' : 'text-stone-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M10 15l-5.9 3.1 1.1-6.6L.4 6.9l6.6-1L10 0l3 5.9 6.6 1-4.8 4.6 1.1 6.6z"/></svg>
                                @endfor
                            </div>
                            <p class="text-stone-200 text-sm leading-relaxed rtl:leading-loose">&ldquo;{{ $testimonial->comment }}&rdquo;</p>
                            <div class="mt-4 flex items-center gap-3">
                                <div class="h-9 w-9 rounded-full overflow-hidden shrink-0">
                                    <x-media-image :model="$testimonial" collection="client_photo" conversion="thumb" alt="" :responsive="false" :width="200" :height="200" :placeholder-icon="false" class="h-full w-full" />
                                </div>
                                <p class="font-semibold text-sm">{{ $testimonial->client_name }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    @if ($faqs->isNotEmpty())
        <section x-reveal class="py-20 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-section-title font-bold text-charcoal text-center mb-12">{{ __('site.home.faq') }}</h2>
            <div class="space-y-3">
                @foreach ($faqs as $faq)
                    <div x-data="{ open: false }" class="rounded-lg border border-stone-200 bg-white">
                        <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="home-faq-{{ $faq->id }}" class="w-full flex items-center justify-between gap-4 p-5 text-start">
                            <span class="font-medium text-charcoal">{{ $faq->question }}</span>
                            <svg class="h-5 w-5 text-stone-500 shrink-0 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="home-faq-{{ $faq->id }}" x-show="open" x-cloak x-transition class="px-5 pb-5 text-sm text-stone-600 leading-relaxed rtl:leading-loose">
                            {{ $faq->answer }}
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ route('faq') }}" class="inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-charcoal hover:underline">
                    {{ __('site.common.view_all') }} <x-heroicon-o-arrow-right class="h-4 w-4 rtl:rotate-180" aria-hidden="true" />
                </a>
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <section x-reveal class="relative bg-stone-100 py-16 overflow-hidden">
        <img src="{{ asset('images/textures/marble-texture.webp') }}" alt="" aria-hidden="true" width="960" height="1280" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover opacity-[0.07]">
        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-section-title font-bold text-charcoal">{{ __('site.home.cta_title') }}</h2>
            <p class="mt-3 text-stone-700">{{ __('site.home.cta_subtitle') }}</p>
            <x-button :href="route('contact')" class="mt-8">
                {{ __('site.common.request_quote') }}
            </x-button>
        </div>
    </section>
</x-layout>
