<x-layout :title="__('site.services.title')" :description="__('site.services.meta_description')" :breadcrumbs="[['name' => __('site.services.title'), 'url' => route('services.index')]]">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-page-title font-bold text-charcoal">{{ __('site.services.title') }}</h1>
        </div>
    </section>

    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($services as $service)
                <x-cards.service :service="$service" heading-tag="h2" :full="true" />
            @empty
                <p class="text-stone-600 col-span-full text-center py-12">{{ __('site.common.no_results') }}</p>
            @endforelse
        </div>
    </section>
</x-layout>
