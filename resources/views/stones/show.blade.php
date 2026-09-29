<x-layout :title="$stoneType->name" :description="$stoneType->description">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
            <div class="aspect-square rounded-xl bg-stone-200 overflow-hidden">
                @if ($stoneType->getFirstMediaUrl('cover'))
                    <img src="{{ $stoneType->getFirstMediaUrl('cover', 'medium') }}" alt="{{ $stoneType->name }}" class="h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04]" loading="eager" fetchpriority="high">
                @else
                    <x-image-placeholder class="h-full w-full" />
                @endif
            </div>
            <div>
                <p class="text-sm font-semibold text-stone-600">{{ $stoneType->stoneCategory->name }}</p>
                <h1 class="text-3xl sm:text-4xl font-bold text-charcoal mt-1">{{ $stoneType->name }}</h1>
                @if ($stoneType->origin)
                    <p class="mt-3 text-sm text-stone-600"><span class="font-semibold">{{ __('site.stones.origin') }}:</span> {{ $stoneType->origin }}</p>
                @endif
                <p class="mt-4 text-stone-600 leading-relaxed rtl:leading-loose">{{ $stoneType->description }}</p>

                @php
                    $specGroups = $stoneType->specificationGroups();
                @endphp
                @if ($specGroups->isNotEmpty() || $stoneType->origin_type)
                    <div class="mt-6 border-t border-stone-200 pt-5">
                        <h2 class="text-sm font-semibold text-charcoal mb-3">{{ __('site.stones.specifications') }}</h2>
                        <dl class="space-y-3 text-sm">
                            @if ($stoneType->origin_type)
                                <div class="flex flex-wrap gap-x-3 gap-y-1">
                                    <dt class="font-semibold text-stone-600 min-w-28">{{ __('site.stones.origin_type') }}</dt>
                                    <dd class="text-charcoal">{{ __('site.common.'.$stoneType->origin_type) }}</dd>
                                </div>
                            @endif
                            @foreach ($specGroups as $type => $values)
                                <div class="flex flex-wrap gap-x-3 gap-y-1">
                                    <dt class="font-semibold text-stone-600 min-w-28">{{ __('site.stones.'.$type) }}</dt>
                                    <dd class="flex flex-wrap gap-2">
                                        @foreach ($values as $value)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-stone-100 px-3 py-1 text-charcoal">
                                                @if ($type === \App\Models\StoneAttribute::COLOUR && $value->swatch)
                                                    <span class="h-3 w-3 rounded-full border border-stone-300" style="background-color: {{ $value->swatch }}" aria-hidden="true"></span>
                                                @endif
                                                {{ $value->name }}
                                            </span>
                                        @endforeach
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
                <x-button :href="route('contact', ['stone_type' => $stoneType->slug])" class="mt-6">
                    {{ __('site.common.request_quote') }}
                </x-button>
            </div>
        </div>
    </section>

    @if ($stoneType->getMedia('gallery')->isNotEmpty())
        <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ open: false, active: 0, count: {{ $stoneType->getMedia('gallery')->count() }}, touchX: 0, next() { this.active = (this.active + 1) % this.count }, prev() { this.active = (this.active - 1 + this.count) % this.count } }">
            <h2 class="text-2xl sm:text-3xl font-bold text-charcoal mb-8">{{ __('site.projects.gallery') }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($stoneType->getMedia('gallery') as $index => $media)
                    <button @click="active = {{ $index }}; open = true" class="aspect-square rounded-lg overflow-hidden bg-stone-200" aria-label="{{ __('site.common.view_image') }}: {{ $stoneType->name }}">
                        <img src="{{ $media->getUrl('thumb') }}" alt="{{ $stoneType->name }}" loading="lazy" class="h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04] hover:scale-105 transition-transform duration-300">
                    </button>
                @endforeach
            </div>

            <div x-show="open" x-cloak x-transition.opacity.duration.200ms class="fixed inset-0 z-50 bg-charcoal/90 flex items-center justify-center p-4"
                 @click.self="open = false"
                 @keydown.escape.window="open = false"
                 @keydown.arrow-right.window="next()"
                 @keydown.arrow-left.window="prev()"
                 @touchstart="touchX = $event.changedTouches[0].screenX"
                 @touchend="const dx = $event.changedTouches[0].screenX - touchX; if (Math.abs(dx) > 40) { dx < 0 ? next() : prev() }">
                <button @click="open = false" class="absolute top-6 end-6 text-cream" aria-label="{{ __('site.common.close') }}">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                @if ($stoneType->getMedia('gallery')->count() > 1)
                    <button @click.stop="prev()" class="absolute start-2 sm:start-4 top-1/2 -translate-y-1/2 h-11 w-11 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-cream" aria-label="{{ __('site.common.previous_image') }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button @click.stop="next()" class="absolute end-2 sm:end-4 top-1/2 -translate-y-1/2 h-11 w-11 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-cream" aria-label="{{ __('site.common.next_image') }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                @endif
                @foreach ($stoneType->getMedia('gallery') as $index => $media)
                    <img x-show="active === {{ $index }}" src="{{ $media->getUrl('medium') }}" alt="{{ $stoneType->name }}" loading="lazy" class="max-h-[85vh] max-w-full rounded-lg saturate-[0.92] contrast-[1.03] sepia-[0.04]">
                @endforeach
            </div>
        </section>
    @endif

    @if ($projects->isNotEmpty())
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-charcoal mb-8">{{ __('site.services.related_projects') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($projects as $project)
                        <a href="{{ route('projects.show', $project) }}" class="group block rounded-xl overflow-hidden border border-stone-200 bg-white hover:shadow-lg hover:-translate-y-1 transition-all">
                            <div class="aspect-[4/3] bg-stone-200 overflow-hidden">
                                @if ($project->getFirstMediaUrl('cover'))
                                    <img src="{{ $project->getFirstMediaUrl('cover', 'medium') }}" alt="{{ $project->title }}" loading="lazy" class="h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04] group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <x-image-placeholder class="h-full w-full" />
                                @endif
                            </div>
                            <div class="p-5">
                                <h3 class="font-semibold text-charcoal">{{ $project->title }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layout>
