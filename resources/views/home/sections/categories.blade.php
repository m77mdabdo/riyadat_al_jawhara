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
                        $tileSrcset = $category->coverImageUrl('card') ? null : \App\Support\SiteImages::srcset($tileSlot);
                    @endphp
                    <a href="{{ route('stones.index', ['stone_category' => $category->slug]) }}" x-reveal style="--reveal-delay: {{ $loop->index * 90 }}ms"
                       class="group relative flex min-h-48 flex-col justify-end overflow-hidden rounded-xl border border-sand-200 bg-sand-100 p-6 transition-all hover:-translate-y-1 hover:shadow-lg">
                        @if ($tileUrl)
                            <img src="{{ $tileUrl }}" @if ($tileSrcset) srcset="{{ $tileSrcset }}" sizes="(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw" @endif alt="" aria-hidden="true" width="1280" height="853" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
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
