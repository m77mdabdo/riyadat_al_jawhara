<x-layout :title="__('site.stones.title')" :description="__('site.stones.meta_description')" :breadcrumbs="[['name' => __('site.stones.title'), 'url' => route('stones.index')]]">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-page-title font-bold text-charcoal">{{ __('site.stones.title') }}</h1>
        </div>
    </section>

    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{
            active: 0,
            stones: [
                @foreach ($stoneTypes as $stoneType)
                {
                    name: @js($stoneType->name),
                    origin: @js($stoneType->origin),
                    description: @js(\Illuminate\Support\Str::limit($stoneType->description, 200)),
                    image: @js(($cover = $stoneType->getFirstMedia('cover')) ? ($cover->hasGeneratedConversion('medium') ? $cover->getUrl('medium') : $cover->getUrl()) : null),
                    url: @js(route('stones.show', $stoneType)),
                },
                @endforeach
            ],
        }">
        @if ($stoneTypes->isNotEmpty())
            <div x-ref="preview" class="mb-12 grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 lg:items-center scroll-mt-24" aria-live="polite">
                <div class="aspect-[4/3] rounded-xl overflow-hidden bg-stone-200 relative">
                    <template x-for="(stone, index) in stones" :key="index">
                        <div x-show="active === index" x-cloak x-transition.opacity.duration.300ms class="absolute inset-0">
                            {{-- Stones without a cover show the same placeholder as the cards (no empty/broken <img>). --}}
                            <template x-if="stone.image">
                                <img :src="stone.image" :alt="stone.name" width="1200" height="700" decoding="async" class="h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04]">
                            </template>
                            <template x-if="! stone.image">
                                <x-image-placeholder class="h-full w-full" />
                            </template>
                        </div>
                    </template>
                </div>
                {{-- Panels share one grid cell, so the box always fits the longest text
                     instead of relying on a fixed min-height (text can't overflow). --}}
                <div class="grid">
                    <template x-for="(stone, index) in stones" :key="index">
                        <div x-show="active === index" x-cloak x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="col-start-1 row-start-1 min-w-0">
                            <p class="text-sm font-semibold text-stone-600" x-show="stone.origin" x-text="stone.origin"></p>
                            <h2 class="text-section-title font-bold text-charcoal mt-1 break-words" x-text="stone.name"></h2>
                            <p class="mt-4 text-stone-600 leading-relaxed rtl:leading-loose break-words" x-text="stone.description"></p>
                            <a :href="stone.url" class="inline-flex min-h-11 items-center gap-1 mt-4 text-sm font-semibold text-charcoal hover:underline">
                                {{ __('site.common.view_details') }} <x-heroicon-o-arrow-right class="h-4 w-4 rtl:rotate-180" aria-hidden="true" />
                            </a>
                        </div>
                    </template>
                </div>
            </div>
        @endif

        @php
            $keptFilters = array_filter($activeFilters);
        @endphp

        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('stones.index', $keptFilters) }}" class="inline-flex min-h-11 items-center px-4 py-2 rounded-full text-sm font-medium {{ $activeCategory === '' ? 'bg-charcoal text-cream' : 'bg-white border border-stone-300 text-stone-700 hover:bg-stone-100' }}">
                {{ __('site.common.all') }}
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('stones.index', $keptFilters + ['stone_category' => $category->slug]) }}" class="inline-flex min-h-11 items-center px-4 py-2 rounded-full text-sm font-medium {{ $activeCategory === $category->slug ? 'bg-charcoal text-cream' : 'bg-white border border-stone-300 text-stone-700 hover:bg-stone-100' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        @if ($filterOptions->isNotEmpty() || $originOptions->isNotEmpty())
            <form method="GET" action="{{ route('stones.index') }}" class="mb-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end" aria-label="{{ __('site.stones.filter_by') }}">
                @if ($activeCategory !== '')
                    <input type="hidden" name="stone_category" value="{{ $activeCategory }}">
                @endif

                @foreach ($attributeFilters as $param => $type)
                    @if ($filterOptions->has($type))
                        <div>
                            <label for="filter-{{ $param }}" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.stones.'.$param) }}</label>
                            <select name="{{ $param }}" id="filter-{{ $param }}" onchange="this.form.submit()" class="w-full min-h-11 rounded-md border-stone-300 px-3 py-2.5 text-base sm:text-sm text-charcoal focus:border-charcoal focus:ring-charcoal">
                                <option value="">{{ __('site.stones.any') }}</option>
                                @foreach ($filterOptions->get($type) as $option)
                                    <option value="{{ $option->slug }}" @selected($activeFilters[$param] === $option->slug)>{{ $option->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                @endforeach

                @if ($originOptions->isNotEmpty())
                    <div>
                        <label for="filter-origin" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.stones.origin_type') }}</label>
                        <select name="origin" id="filter-origin" onchange="this.form.submit()" class="w-full min-h-11 rounded-md border-stone-300 px-3 py-2.5 text-base sm:text-sm text-charcoal focus:border-charcoal focus:ring-charcoal">
                            <option value="">{{ __('site.stones.any') }}</option>
                            @foreach ($originOptions as $originOption)
                                <option value="{{ $originOption }}" @selected($activeFilters['origin'] === $originOption)>{{ __('site.common.'.$originOption) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-center gap-4">
                    <noscript><button type="submit" class="rounded-md bg-charcoal text-cream px-5 py-2.5 text-sm font-semibold">{{ __('site.common.filter') }}</button></noscript>
                    @if ($keptFilters !== [])
                        <a href="{{ route('stones.index', array_filter(['stone_category' => $activeCategory])) }}" class="inline-flex min-h-11 items-center text-sm font-medium text-stone-600 hover:text-charcoal">{{ __('site.stones.clear_filters') }} &times;</a>
                    @endif
                </div>
            </form>
        @endif

        <p class="sr-only" role="status">{{ __('site.stones.results_count', ['count' => $stoneTypes->count()]) }}</p>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse ($stoneTypes as $index => $stoneType)
                <button type="button" @click="active = {{ $index }}; if ($refs.preview && $refs.preview.getBoundingClientRect().top < 80) $refs.preview.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' })" class="group block rounded-xl overflow-hidden border text-start transition-all" :class="active === {{ $index }} ? 'border-charcoal shadow-lg -translate-y-1' : 'border-stone-200 bg-white hover:shadow-lg hover:-translate-y-1'" :aria-pressed="active === {{ $index }}">
                    <div class="aspect-square bg-stone-200 overflow-hidden">
                        <x-media-image :model="$stoneType" conversion="thumb" :alt="$stoneType->name" class="h-full w-full" img-class="group-hover:scale-105 transition-transform duration-500" />
                    </div>
                    <div class="p-4">
                        <h2 class="font-semibold text-charcoal text-sm break-words">{{ $stoneType->name }}</h2>
                        @if ($stoneType->origin)
                            <p class="text-sm sm:text-xs text-stone-600 mt-1">{{ $stoneType->origin }}</p>
                        @endif
                    </div>
                </button>
            @empty
                <p class="text-stone-600 col-span-full text-center py-12">{{ __('site.common.no_results') }}</p>
            @endforelse
        </div>
    </section>
</x-layout>
