<x-layout :title="$stoneType->name" :description="$stoneType->description" :image="$stoneType->coverImageUrl()" :breadcrumbs="[['name' => __('site.stones.title'), 'url' => route('stones.index')], ['name' => $stoneType->name, 'url' => route('stones.show', $stoneType)]]">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
            <div class="aspect-square rounded-xl bg-stone-200 overflow-hidden">
                <x-media-image :model="$stoneType" :alt="$stoneType->name" :eager="true" sizes="(min-width: 768px) 480px, 100vw" class="h-full w-full" />
            </div>
            <div>
                <p class="text-sm font-semibold text-stone-600">{{ $stoneType->stoneCategory->name }}</p>
                <h1 class="text-page-title font-bold text-charcoal mt-1">{{ $stoneType->name }}</h1>
                @if ($stoneType->origin)
                    <p class="mt-3 text-sm text-stone-600"><span class="font-semibold">{{ __('site.stones.origin') }}:</span> {{ $stoneType->origin }}</p>
                @endif
                <p class="mt-4 text-stone-600 leading-relaxed rtl:leading-loose break-words">{{ $stoneType->description }}</p>

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
        <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-section-title font-bold text-charcoal mb-8">{{ __('site.projects.gallery') }}</h2>
            <x-lightbox :media="$stoneType->getMedia('gallery')" :alt="$stoneType->name" />
        </section>
    @endif

    @if ($projects->isNotEmpty())
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-section-title font-bold text-charcoal mb-8">{{ __('site.services.related_projects') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($projects as $project)
                        <x-cards.project :project="$project" :show-location="false" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layout>
