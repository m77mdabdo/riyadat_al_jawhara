<x-layout :title="$service->name" :description="$service->description">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($service->getFirstMediaUrl('cover'))
                <div class="aspect-[21/9] rounded-xl overflow-hidden mb-8">
                    <img src="{{ $service->getFirstMediaUrl('cover', 'medium') }}" alt="{{ $service->name }}" loading="eager" fetchpriority="high" class="h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04]">
                </div>
            @endif
            <div class="h-14 w-14 rounded-lg bg-charcoal flex items-center justify-center mb-6">
                <x-dynamic-component :component="$service->safe_icon" class="h-7 w-7 text-cream" />
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold text-charcoal">{{ $service->name }}</h1>
            <p class="mt-4 text-stone-600 leading-relaxed max-w-3xl">{{ $service->description }}</p>
        </div>
    </section>

    @if ($projects->isNotEmpty())
        <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                            <p class="text-sm text-stone-600 mt-1">{{ $project->location }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="bg-stone-100 py-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <x-button :href="route('contact')">
                {{ __('site.common.request_quote') }}
            </x-button>
        </div>
    </section>
</x-layout>
