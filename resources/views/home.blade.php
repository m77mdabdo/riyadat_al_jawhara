<x-layout>
    {{-- Hero --}}
    <section class="relative bg-charcoal text-cream overflow-hidden" x-data="{ active: 0, count: {{ $sliders->count() ?: 1 }} }" x-init="setInterval(() => active = (active + 1) % count, 6000)">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 20%, #8B8680 0, transparent 40%), radial-gradient(circle at 80% 80%, #8B8680 0, transparent 40%);"></div>

        @forelse ($sliders as $index => $slider)
            <div x-show="active === {{ $index }}" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak="{{ $index > 0 ? 'true' : 'false' }}" class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32 text-center">
                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">{{ $slider->title }}</h1>
                @if ($slider->subtitle)
                    <p class="mt-5 text-lg text-stone-300 max-w-2xl mx-auto">{{ $slider->subtitle }}</p>
                @endif
                @if ($slider->button_text && $slider->button_link)
                    <a href="{{ $slider->button_link }}" class="mt-8 inline-flex items-center rounded-md bg-cream px-7 py-3 text-sm font-semibold text-charcoal hover:bg-stone-100 transition-colors">
                        {{ $slider->button_text }}
                    </a>
                @endif
            </div>
        @empty
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32 text-center">
                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">{{ __('site.meta.site_name') }}</h1>
                <p class="mt-5 text-lg text-stone-300">{{ __('site.meta.tagline') }}</p>
            </div>
        @endforelse

        @if ($sliders->count() > 1)
            <div class="relative flex items-center justify-center gap-2 pb-8">
                @foreach ($sliders as $index => $slider)
                    <button @click="active = {{ $index }}" class="h-2 w-2 rounded-full" :class="active === {{ $index }} ? 'bg-cream' : 'bg-stone-500'"></button>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Services --}}
    <section class="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-charcoal">{{ __('site.home.our_services') }}</h2>
            <p class="mt-3 text-stone-600">{{ __('site.home.our_services_subtitle') }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($services as $service)
                <a href="{{ route('services.show', $service) }}" class="group rounded-xl border border-stone-200 bg-white p-6 hover:shadow-lg hover:-translate-y-1 transition-all">
                    <div class="h-12 w-12 rounded-lg bg-charcoal/5 flex items-center justify-center mb-4 group-hover:bg-charcoal transition-colors">
                        <x-dynamic-component :component="$service->icon ?: 'heroicon-o-cube'" class="h-6 w-6 text-charcoal group-hover:text-cream transition-colors" />
                    </div>
                    <h3 class="font-semibold text-charcoal mb-2">{{ $service->name }}</h3>
                    <p class="text-sm text-stone-600 line-clamp-2">{{ $service->description }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Featured Projects --}}
    @if ($featuredProjects->isNotEmpty())
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-12">
                    <h2 class="text-2xl sm:text-3xl font-bold text-charcoal">{{ __('site.home.featured_projects') }}</h2>
                    <a href="{{ route('projects.index') }}" class="text-sm font-semibold text-charcoal hover:underline">{{ __('site.common.view_all') }} &rarr;</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($featuredProjects as $project)
                        <a href="{{ route('projects.show', $project) }}" class="group block rounded-xl overflow-hidden border border-stone-200">
                            <div class="aspect-[4/3] bg-stone-200 overflow-hidden">
                                @if ($project->getFirstMediaUrl('cover'))
                                    <img src="{{ $project->getFirstMediaUrl('cover', 'medium') }}" alt="{{ $project->title }}" loading="lazy" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @endif
                            </div>
                            <div class="p-5">
                                <h3 class="font-semibold text-charcoal">{{ $project->title }}</h3>
                                <p class="text-sm text-stone-600 mt-1">{{ $project->location }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Stone Collection --}}
    @if ($stoneTypes->isNotEmpty())
        <section class="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-12">
                <h2 class="text-2xl sm:text-3xl font-bold text-charcoal">{{ __('site.home.stone_collection') }}</h2>
                <a href="{{ route('stones.index') }}" class="text-sm font-semibold text-charcoal hover:underline">{{ __('site.common.view_all') }} &rarr;</a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($stoneTypes as $stoneType)
                    <a href="{{ route('stones.show', $stoneType) }}" class="group block rounded-xl overflow-hidden border border-stone-200 bg-white">
                        <div class="aspect-square bg-stone-200 overflow-hidden">
                            @if ($stoneType->getFirstMediaUrl('cover'))
                                <img src="{{ $stoneType->getFirstMediaUrl('cover', 'thumb') }}" alt="{{ $stoneType->name }}" loading="lazy" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="font-medium text-charcoal text-sm">{{ $stoneType->name }}</h3>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Testimonials --}}
    @if ($testimonials->isNotEmpty())
        <section class="py-20 bg-charcoal text-cream">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-center mb-12">{{ __('site.home.testimonials') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" x-data>
                    @foreach ($testimonials as $testimonial)
                        <div class="rounded-xl bg-white/5 border border-white/10 p-6">
                            <div class="flex gap-1 mb-3 text-stone-300">
                                @for ($i = 0; $i < 5; $i++)
                                    <svg class="h-4 w-4 {{ $i < $testimonial->rating ? 'text-cream' : 'text-stone-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M10 15l-5.9 3.1 1.1-6.6L.4 6.9l6.6-1L10 0l3 5.9 6.6 1-4.8 4.6 1.1 6.6z"/></svg>
                                @endfor
                            </div>
                            <p class="text-stone-200 text-sm leading-relaxed">&ldquo;{{ $testimonial->comment }}&rdquo;</p>
                            <p class="mt-4 font-semibold text-sm">{{ $testimonial->client_name }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    @if ($faqs->isNotEmpty())
        <section class="py-20 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-charcoal text-center mb-12">{{ __('site.home.faq') }}</h2>
            <div class="space-y-3">
                @foreach ($faqs as $faq)
                    <div x-data="{ open: false }" class="rounded-lg border border-stone-200 bg-white">
                        <button @click="open = !open" class="w-full flex items-center justify-between p-5 text-start">
                            <span class="font-medium text-charcoal">{{ $faq->question }}</span>
                            <svg class="h-5 w-5 text-stone-500 shrink-0 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak x-transition class="px-5 pb-5 text-sm text-stone-600 leading-relaxed">
                            {{ $faq->answer }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <section class="bg-stone-100 py-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl sm:text-3xl font-bold text-charcoal">{{ __('site.home.cta_title') }}</h2>
            <p class="mt-3 text-stone-600">{{ __('site.home.cta_subtitle') }}</p>
            <a href="{{ route('contact') }}" class="mt-8 inline-flex items-center rounded-md bg-charcoal px-7 py-3 text-sm font-semibold text-cream hover:bg-charcoal-700 transition-colors">
                {{ __('site.common.request_quote') }}
            </a>
        </div>
    </section>
</x-layout>
