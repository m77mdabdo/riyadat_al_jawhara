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
    @endphp

    @push('head')
        <link rel="preload" as="image" href="{{ $firstSlideImage ?? $heroFallback }}" fetchpriority="high">
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
            <img src="{{ $heroFallback }}" alt="" width="1920" height="1280" decoding="async" class="absolute inset-0 h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04]" @if ($firstSlideImage) loading="lazy" @else loading="eager" fetchpriority="high" @endif>
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
                                <x-button :href="$slider->button_link" variant="inverted">{{ $slider->button_text }}</x-button>
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

    {{-- Why choose us --}}
    <section class="relative overflow-hidden bg-sand-100">
        <x-motif.geo-pattern />
        <div x-reveal class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <x-section-heading :title="__('site.home.why_choose_us')" :subtitle="__('site.home.why_choose_us_subtitle')" class="mb-12" />
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach (__('site.home.why_items') as $index => $item)
                    <div x-reveal style="--reveal-delay: {{ $index * 90 }}ms" class="group rounded-xl border border-sand-200 bg-cream p-6 transition-all hover:-translate-y-1 hover:shadow-lg">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-charcoal text-gold-400">
                            <x-dynamic-component :component="$item['icon']" class="h-6 w-6" aria-hidden="true" />
                        </span>
                        <h3 class="mt-4 font-semibold text-charcoal">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-sm text-stone-700 leading-relaxed rtl:leading-loose">{{ $item['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Services --}}
    <section x-reveal class="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-section-heading :title="__('site.home.our_services')" :subtitle="__('site.home.our_services_subtitle')" class="mb-12" />
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($services as $service)
                <x-cards.service :service="$service" x-reveal style="--reveal-delay: {{ $loop->index * 90 }}ms" />
            @endforeach
        </div>
    </section>

    {{-- Stone categories: texture tiles into the filtered catalogue --}}
    @if ($stoneCategories->isNotEmpty())
        <section class="bg-white py-20">
            <div x-reveal class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <x-section-heading :title="__('site.home.categories_title')" :subtitle="__('site.home.categories_subtitle')" class="mb-12" />
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($stoneCategories as $category)
                        @php
                            $tileSlot = config('site-images.category_tiles.'.$category->slug, 'texture-marble');
                            $tileUrl = $category->coverImageUrl('card') ?? \App\Support\SiteImages::url($tileSlot);
                        @endphp
                        <a href="{{ route('stones.index', ['stone_category' => $category->slug]) }}" x-reveal style="--reveal-delay: {{ $loop->index * 90 }}ms"
                           class="group relative flex min-h-48 flex-col justify-end overflow-hidden rounded-xl border border-sand-200 bg-sand-100 p-6 transition-all hover:-translate-y-1 hover:shadow-lg">
                            @if ($tileUrl)
                                <img src="{{ $tileUrl }}" alt="" aria-hidden="true" width="1280" height="853" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                            @endif
                            <span class="absolute inset-0 bg-gradient-to-t from-charcoal/85 via-charcoal/40 to-transparent" aria-hidden="true"></span>
                            <span class="relative">
                                <span class="block text-lg font-semibold text-cream font-display">{{ $category->name }}</span>
                                <span class="mt-1 block text-sm text-stone-200">{{ trans_choice('site.home.stones_count', $category->stone_types_count, ['count' => $category->stone_types_count]) }}</span>
                                <span class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-gold-400">
                                    <span class="link-sweep">{{ __('site.home.categories_cta', ['name' => $category->name]) }}</span>
                                    <x-heroicon-o-arrow-right class="h-4 w-4 rtl:rotate-180" aria-hidden="true" />
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-motif.najdi-band />

    {{-- Featured Projects (mashrabiya-framed) --}}
    @if ($featuredProjects->isNotEmpty())
        <section class="py-20">
            <div x-reveal class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4 mb-12">
                    <x-section-heading :title="__('site.home.featured_projects')" align="start" />
                    <a href="{{ route('projects.index') }}" class="link-sweep inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-charcoal">{{ __('site.common.view_all') }} <x-heroicon-o-arrow-right class="h-4 w-4 rtl:rotate-180" aria-hidden="true" /></a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($featuredProjects as $project)
                        <x-motif.mashrabiya-frame x-reveal style="--reveal-delay: {{ $loop->index * 90 }}ms">
                            <x-cards.project :project="$project" />
                        </x-motif.mashrabiya-frame>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Stone Collection --}}
    @if ($stoneTypes->isNotEmpty())
        <section x-reveal class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4 mb-12">
                    <x-section-heading :title="__('site.home.stone_collection')" align="start" />
                    <a href="{{ route('stones.index') }}" class="link-sweep inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-charcoal">{{ __('site.common.view_all') }} <x-heroicon-o-arrow-right class="h-4 w-4 rtl:rotate-180" aria-hidden="true" /></a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach ($stoneTypes as $stoneType)
                        <x-cards.stone :stone-type="$stoneType" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- How We Work: Najdi-frieze connectors --}}
    <section class="relative overflow-hidden bg-sand-100">
        <x-motif.geo-pattern />
        <div x-reveal class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <x-section-heading :title="__('site.process.title')" :subtitle="__('site.process.subtitle')" class="mb-16" />
            <div class="relative">
                <x-motif.najdi-band class="hidden lg:flex absolute inset-x-[10%] top-3 !py-0" tone="sand" />
                <span class="lg:hidden absolute top-0 bottom-0 start-6 w-px bg-sand-300" aria-hidden="true"></span>
            <ol class="relative grid grid-cols-1 lg:grid-cols-5 gap-10 lg:gap-6">
                @foreach (__('site.process.steps') as $index => $step)
                    <li x-reveal style="--reveal-delay: {{ $index * 110 }}ms" class="relative flex items-start lg:flex-col lg:items-center gap-4 lg:gap-0 lg:text-center">
                        <span class="relative z-10 shrink-0 h-12 w-12 rounded-full bg-charcoal text-gold-400 ring-4 ring-sand-100 flex items-center justify-center font-bold" aria-hidden="true">{{ $index + 1 }}</span>
                        <div class="lg:mt-5">
                            <h3 class="font-semibold text-charcoal">{{ $step['title'] }}</h3>
                            <p class="mt-1 text-sm text-stone-700 leading-relaxed rtl:leading-loose">{{ $step['description'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
            </div>
        </div>
    </section>

    {{-- Stats: Settings-driven only, hidden when no figure is set --}}
    @php $stats = $settings->stats(); @endphp
    @if ($stats !== [])
        <section class="bg-charcoal text-cream py-16" aria-label="{{ __('site.home.stats_title') }}">
            <dl @class([
                'max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid gap-8 text-center',
                'grid-cols-2 lg:grid-cols-4' => count($stats) >= 3,
                'grid-cols-2' => count($stats) === 2,
                'grid-cols-1' => count($stats) === 1,
            ])>
                @foreach ($stats as $key => $value)
                    <div class="flex flex-col-reverse" x-data="{ shown: {{ $value }}, target: {{ $value }} }"
                         x-init="if (! window.matchMedia('(prefers-reduced-motion: reduce)').matches) { shown = 0; const io = new IntersectionObserver((e) => { if (e[0].isIntersecting) { io.disconnect(); const t0 = performance.now(); const step = (t) => { const p = Math.min(1, (t - t0) / 1200); shown = Math.round(target * p); if (p < 1) requestAnimationFrame(step); }; requestAnimationFrame(step); } }); io.observe($el); }">
                        <dt class="mt-2 text-sm text-stone-300">{{ __('site.home.stats.'.$key) }}</dt>
                        <dd class="text-4xl font-bold text-gold-400 font-display"><bdi x-text="shown.toLocaleString()">{{ number_format($value) }}</bdi>+</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @endif

    {{-- Heritage in stone (inspiration imagery, clearly labelled) --}}
    <section class="py-20">
        <div x-reveal class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-section-heading :title="__('site.home.heritage_title')" :subtitle="__('site.home.heritage_subtitle')" class="mb-12" />
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach (__('site.home.heritage_items') as $key => $item)
                    @php $heritageUrl = \App\Support\SiteImages::url('heritage-'.$key); @endphp
                    <figure x-reveal style="--reveal-delay: {{ $loop->index * 90 }}ms" class="group overflow-hidden rounded-xl border border-sand-200 bg-cream">
                        <div class="relative aspect-[4/3] overflow-hidden bg-sand-100">
                            @if ($heritageUrl)
                                <img src="{{ $heritageUrl }}" alt="{{ $item['name'] }}: {{ $item['caption'] }}" width="1880" height="1253" loading="lazy" decoding="async" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                            @else
                                {{-- No verified photo of this place: an honest pattern card instead. --}}
                                <div class="absolute inset-0 flex items-center justify-center bg-sand-200">
                                    <x-motif.geo-pattern opacity="0.05" />
                                    <x-motif.najdi-band class="w-3/4" tone="gold" />
                                </div>
                            @endif
                        </div>
                        <figcaption class="p-5">
                            <p class="font-semibold text-charcoal">{{ $item['name'] }}</p>
                            <p class="mt-1 text-sm text-stone-700">{{ $item['caption'] }}</p>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
            <p class="mt-6 text-center text-sm text-stone-600">{{ __('site.home.heritage_note') }}</p>
        </div>
    </section>

    {{-- Testimonials --}}
    @if ($testimonials->isNotEmpty())
        <section x-reveal class="relative py-20 bg-charcoal text-cream overflow-hidden">
            <img src="{{ \App\Support\SiteImages::url('texture-granite', asset('images/textures/stone-texture.webp')) }}" alt="" aria-hidden="true" width="1280" height="853" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover opacity-10">
            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <x-section-heading :title="__('site.home.testimonials')" tone="light" class="mb-12" />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" x-data>
                    @foreach ($testimonials as $testimonial)
                        <div class="rounded-xl bg-white/5 border border-white/10 p-6">
                            <div class="flex gap-1 mb-3 text-stone-300">
                                @for ($i = 0; $i < 5; $i++)
                                    <svg aria-hidden="true" class="h-4 w-4 {{ $i < $testimonial->rating ? 'text-gold-400' : 'text-stone-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M10 15l-5.9 3.1 1.1-6.6L.4 6.9l6.6-1L10 0l3 5.9 6.6 1-4.8 4.6 1.1 6.6z"/></svg>
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
            <x-section-heading :title="__('site.home.faq')" class="mb-12" />
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
                <a href="{{ route('faq') }}" class="link-sweep inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-charcoal">
                    {{ __('site.common.view_all') }} <x-heroicon-o-arrow-right class="h-4 w-4 rtl:rotate-180" aria-hidden="true" />
                </a>
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <section x-reveal class="relative bg-sand-100 py-20 overflow-hidden">
        <img src="{{ \App\Support\SiteImages::url('texture-sandstone', asset('images/textures/marble-texture.webp')) }}" alt="" aria-hidden="true" width="1280" height="853" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover opacity-20">
        <div class="absolute inset-0 bg-cream/60" aria-hidden="true"></div>
        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <x-section-heading :title="__('site.home.cta_title')" :subtitle="__('site.home.cta_subtitle')" />
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <x-button :href="route('contact')">{{ __('site.common.request_quote') }}</x-button>
                @if ($settings->whatsapp_url)
                    <x-button :href="$settings->whatsapp_url" variant="outline" data-wa-track target="_blank" rel="noopener noreferrer">
                        <x-social-icon network="whatsapp" class="h-4 w-4" /> {{ __('site.common.whatsapp_us') }}
                    </x-button>
                @endif
            </div>
        </div>
    </section>
</x-layout>
