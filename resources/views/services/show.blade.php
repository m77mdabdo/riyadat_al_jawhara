<x-layout :title="$service->name" :description="$service->description" :image="$service->coverImageUrl()" :breadcrumbs="[['name' => __('site.services.title'), 'url' => route('services.index')], ['name' => $service->name, 'url' => route('services.show', $service)]]">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($service->getFirstMedia('cover'))
                <div class="aspect-[16/9] sm:aspect-[21/9] rounded-xl overflow-hidden mb-8">
                    <x-media-image :model="$service" :alt="$service->name" :eager="true" sizes="(min-width: 1024px) 960px, 100vw" class="h-full w-full" />
                </div>
            @endif
            <div class="h-14 w-14 rounded-lg bg-charcoal flex items-center justify-center mb-6">
                <x-dynamic-component :component="$service->safe_icon" class="h-7 w-7 text-cream" aria-hidden="true" />
            </div>
            <h1 class="text-page-title font-bold text-charcoal">{{ $service->name }}</h1>
            <p class="mt-4 text-stone-600 leading-relaxed rtl:leading-loose max-w-3xl break-words">{{ $service->description }}</p>
        </div>
    </section>

    @if ($projects->isNotEmpty())
        <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-section-title font-bold text-charcoal mb-8">{{ __('site.services.related_projects') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($projects as $project)
                    <x-cards.project :project="$project" />
                @endforeach
            </div>
        </section>
    @endif

    <section class="bg-stone-100 py-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <x-button :href="route('contact', ['service' => $service->slug])">
                {{ __('site.common.request_quote') }}
            </x-button>
        </div>
    </section>
</x-layout>
