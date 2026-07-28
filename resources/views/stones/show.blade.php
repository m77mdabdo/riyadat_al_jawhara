<x-layout :title="$stoneType->name" :description="$stoneType->description">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
            <div class="aspect-square rounded-xl bg-stone-200 overflow-hidden">
                @if ($stoneType->getFirstMediaUrl('cover'))
                    <img src="{{ $stoneType->getFirstMediaUrl('cover', 'medium') }}" alt="{{ $stoneType->name }}" class="h-full w-full object-cover">
                @endif
            </div>
            <div>
                <p class="text-sm font-semibold text-stone-500">{{ $stoneType->stoneCategory->name }}</p>
                <h1 class="text-3xl font-bold text-charcoal mt-1">{{ $stoneType->name }}</h1>
                @if ($stoneType->origin)
                    <p class="mt-3 text-sm text-stone-600"><span class="font-semibold">{{ __('site.stones.origin') }}:</span> {{ $stoneType->origin }}</p>
                @endif
                <p class="mt-4 text-stone-600 leading-relaxed">{{ $stoneType->description }}</p>
                <a href="{{ route('contact') }}" class="mt-6 inline-flex items-center rounded-md bg-charcoal px-6 py-3 text-sm font-semibold text-cream hover:bg-charcoal-700 transition-colors">
                    {{ __('site.common.request_quote') }}
                </a>
            </div>
        </div>
    </section>

    @if ($stoneType->getMedia('gallery')->isNotEmpty())
        <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ open: false, active: 0 }">
            <h2 class="text-2xl font-bold text-charcoal mb-8">{{ __('site.projects.gallery') }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($stoneType->getMedia('gallery') as $index => $media)
                    <button @click="active = {{ $index }}; open = true" class="aspect-square rounded-lg overflow-hidden bg-stone-200">
                        <img src="{{ $media->getUrl('thumb') }}" alt="{{ $stoneType->name }}" loading="lazy" class="h-full w-full object-cover hover:scale-105 transition-transform duration-300">
                    </button>
                @endforeach
            </div>

            <div x-show="open" x-cloak class="fixed inset-0 z-50 bg-charcoal/90 flex items-center justify-center p-4" @click.self="open = false">
                <button @click="open = false" class="absolute top-6 end-6 text-cream" aria-label="{{ __('site.common.close') }}">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                @foreach ($stoneType->getMedia('gallery') as $index => $media)
                    <img x-show="active === {{ $index }}" src="{{ $media->getUrl('medium') }}" alt="{{ $stoneType->name }}" class="max-h-[85vh] max-w-full rounded-lg">
                @endforeach
            </div>
        </section>
    @endif

    @if ($projects->isNotEmpty())
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-charcoal mb-8">{{ __('site.services.related_projects') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($projects as $project)
                        <a href="{{ route('projects.show', $project) }}" class="group block rounded-xl overflow-hidden border border-stone-200">
                            <div class="aspect-[4/3] bg-stone-200 overflow-hidden">
                                @if ($project->getFirstMediaUrl('cover'))
                                    <img src="{{ $project->getFirstMediaUrl('cover', 'medium') }}" alt="{{ $project->title }}" loading="lazy" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500">
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
