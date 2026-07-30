<x-layout :title="__('site.stones.title')">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-charcoal">{{ __('site.stones.title') }}</h1>
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
                    image: @js($stoneType->getFirstMediaUrl('cover', 'medium') ?: null),
                    url: @js(route('stones.show', $stoneType)),
                },
                @endforeach
            ],
        }">
        @if ($stoneTypes->isNotEmpty())
            <div class="mb-12 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:items-center">
                <div class="aspect-[4/3] rounded-xl overflow-hidden bg-stone-200 relative">
                    <template x-for="(stone, index) in stones" :key="index">
                        <img x-show="active === index" x-cloak x-transition.opacity.duration.300ms :src="stone.image" :alt="stone.name" class="absolute inset-0 h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04]">
                    </template>
                </div>
                <div class="relative min-h-[220px] sm:min-h-[180px]">
                    <template x-for="(stone, index) in stones" :key="index">
                        <div x-show="active === index" x-cloak x-transition.opacity.duration.300ms class="absolute inset-0">
                            <p class="text-sm font-semibold text-stone-600" x-text="stone.origin"></p>
                            <h2 class="text-2xl sm:text-3xl font-bold text-charcoal mt-1" x-text="stone.name"></h2>
                            <p class="mt-4 text-stone-600 leading-relaxed rtl:leading-loose" x-text="stone.description"></p>
                            <a :href="stone.url" class="inline-flex items-center gap-1 mt-6 text-sm font-semibold text-charcoal hover:underline">
                                {{ __('site.common.view_details') }} <x-heroicon-o-arrow-right class="h-4 w-4 rtl:rotate-180" />
                            </a>
                        </div>
                    </template>
                </div>
            </div>
        @endif

        <div class="flex flex-wrap gap-2 mb-10">
            <a href="{{ route('stones.index') }}" class="px-4 py-2 rounded-full text-sm font-medium {{ $activeCategory === '' ? 'bg-charcoal text-cream' : 'bg-white border border-stone-300 text-stone-700 hover:bg-stone-100' }}">
                {{ __('site.common.all') }}
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('stones.index', ['stone_category' => $category->slug]) }}" class="px-4 py-2 rounded-full text-sm font-medium {{ $activeCategory === $category->slug ? 'bg-charcoal text-cream' : 'bg-white border border-stone-300 text-stone-700 hover:bg-stone-100' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse ($stoneTypes as $index => $stoneType)
                <button type="button" @click="active = {{ $index }}; $el.scrollIntoView({ behavior: 'smooth', block: 'nearest' })" class="group block rounded-xl overflow-hidden border text-start transition-all" :class="active === {{ $index }} ? 'border-charcoal shadow-lg -translate-y-1' : 'border-stone-200 bg-white hover:shadow-lg hover:-translate-y-1'" :aria-pressed="active === {{ $index }}">
                    <div class="aspect-square bg-stone-200 overflow-hidden">
                        @if ($stoneType->getFirstMediaUrl('cover'))
                            <img src="{{ $stoneType->getFirstMediaUrl('cover', 'thumb') }}" alt="{{ $stoneType->name }}" loading="lazy" class="h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04] group-hover:scale-105 transition-transform duration-500">
                        @else
                            <x-image-placeholder class="h-full w-full" />
                        @endif
                    </div>
                    <div class="p-4">
                        <h2 class="font-semibold text-charcoal text-sm">{{ $stoneType->name }}</h2>
                        @if ($stoneType->origin)
                            <p class="text-xs text-stone-600 mt-1">{{ $stoneType->origin }}</p>
                        @endif
                    </div>
                </button>
            @empty
                <p class="text-stone-600 col-span-full text-center py-12">{{ __('site.common.no_results') }}</p>
            @endforelse
        </div>
    </section>
</x-layout>
