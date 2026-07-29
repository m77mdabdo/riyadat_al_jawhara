<x-layout :title="__('site.services.title')">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-charcoal">{{ __('site.services.title') }}</h1>
        </div>
    </section>

    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($services as $service)
                <a href="{{ route('services.show', $service) }}" class="group block rounded-xl overflow-hidden border border-stone-200 bg-white hover:shadow-lg hover:-translate-y-1 transition-all">
                    <div class="aspect-[4/3] bg-stone-200 overflow-hidden relative">
                        @if ($service->getFirstMediaUrl('cover'))
                            <img src="{{ $service->getFirstMediaUrl('cover', 'medium') }}" alt="{{ $service->name }}" loading="lazy" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <x-image-placeholder class="h-full w-full" />
                        @endif
                        <div class="absolute bottom-3 start-3 h-10 w-10 rounded-lg bg-charcoal flex items-center justify-center">
                            <x-dynamic-component :component="$service->icon ?: 'heroicon-o-cube'" class="h-5 w-5 text-cream" />
                        </div>
                    </div>
                    <div class="p-6">
                        <h2 class="font-semibold text-charcoal mb-2">{{ $service->name }}</h2>
                        <p class="text-sm text-stone-600">{{ $service->description }}</p>
                        <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-charcoal group-hover:underline">{{ __('site.common.read_more') }} <x-heroicon-o-arrow-right class="h-4 w-4 rtl:rotate-180" /></span>
                    </div>
                </a>
            @empty
                <p class="text-stone-600 col-span-full text-center py-12">{{ __('site.common.no_results') }}</p>
            @endforelse
        </div>
    </section>
</x-layout>
