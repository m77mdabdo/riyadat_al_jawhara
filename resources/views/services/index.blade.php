<x-layout :title="__('site.services.title')">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-charcoal">{{ __('site.services.title') }}</h1>
        </div>
    </section>

    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($services as $service)
                <a href="{{ route('services.show', $service) }}" class="group rounded-xl border border-stone-200 bg-white p-6 hover:shadow-lg hover:-translate-y-1 transition-all">
                    <div class="h-12 w-12 rounded-lg bg-charcoal/5 flex items-center justify-center mb-4 group-hover:bg-charcoal transition-colors">
                        <x-dynamic-component :component="$service->icon ?: 'heroicon-o-cube'" class="h-6 w-6 text-charcoal group-hover:text-cream transition-colors" />
                    </div>
                    <h3 class="font-semibold text-charcoal mb-2">{{ $service->name }}</h3>
                    <p class="text-sm text-stone-600">{{ $service->description }}</p>
                    <span class="mt-4 inline-flex text-sm font-semibold text-charcoal group-hover:underline">{{ __('site.common.read_more') }} &rarr;</span>
                </a>
            @empty
                <p class="text-stone-600 col-span-full text-center">{{ __('site.common.no_results') }}</p>
            @endforelse
        </div>
    </section>
</x-layout>
