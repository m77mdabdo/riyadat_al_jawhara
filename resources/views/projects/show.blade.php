<x-layout :title="$project->title" :description="$project->description">
    @php
        $allMedia = $project->getMedia('gallery')
            ->merge($project->getMedia('before'))
            ->merge($project->getMedia('after'));
    @endphp

    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="aspect-[16/9] rounded-xl bg-stone-200 overflow-hidden mb-8">
                @if ($project->getFirstMediaUrl('cover'))
                    <img src="{{ $project->getFirstMediaUrl('cover', 'medium') }}" alt="{{ $project->title }}" class="h-full w-full object-cover">
                @endif
            </div>
            <h1 class="text-3xl font-bold text-charcoal">{{ $project->title }}</h1>
            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-stone-600">
                @if ($project->location)
                    <span><span class="font-semibold text-charcoal">{{ __('site.projects.location') }}:</span> {{ $project->location }}</span>
                @endif
                @if ($project->service)
                    <span><span class="font-semibold text-charcoal">{{ __('site.projects.service') }}:</span> {{ $project->service->name }}</span>
                @endif
                @if ($project->stoneType)
                    <span><span class="font-semibold text-charcoal">{{ __('site.projects.stone_type') }}:</span> {{ $project->stoneType->name }}</span>
                @endif
                @if ($project->completed_at)
                    <span><span class="font-semibold text-charcoal">{{ __('site.projects.completed_at') }}:</span> {{ $project->completed_at->format('Y-m-d') }}</span>
                @endif
            </div>
            <p class="mt-6 text-stone-600 leading-relaxed">{{ $project->description }}</p>
        </div>
    </section>

    @if ($project->getFirstMediaUrl('before') && $project->getFirstMediaUrl('after'))
        <section class="py-16 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-charcoal mb-8">{{ __('site.projects.before') }} / {{ __('site.projects.after') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm font-semibold text-stone-500 mb-2">{{ __('site.projects.before') }}</p>
                    <div class="aspect-[4/3] rounded-xl bg-stone-200 overflow-hidden">
                        <img src="{{ $project->getFirstMediaUrl('before', 'medium') }}" alt="Before" class="h-full w-full object-cover">
                    </div>
                </div>
                <div>
                    <p class="text-sm font-semibold text-stone-500 mb-2">{{ __('site.projects.after') }}</p>
                    <div class="aspect-[4/3] rounded-xl bg-stone-200 overflow-hidden">
                        <img src="{{ $project->getFirstMediaUrl('after', 'medium') }}" alt="After" class="h-full w-full object-cover">
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($allMedia->isNotEmpty())
        <section class="py-16 bg-white" x-data="{ open: false, active: 0 }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-charcoal mb-8">{{ __('site.projects.gallery') }}</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($allMedia as $index => $media)
                        <button @click="active = {{ $index }}; open = true" class="aspect-square rounded-lg overflow-hidden bg-stone-200">
                            <img src="{{ $media->getUrl('thumb') }}" alt="{{ $project->title }}" loading="lazy" class="h-full w-full object-cover hover:scale-105 transition-transform duration-300">
                        </button>
                    @endforeach
                </div>
            </div>

            <div x-show="open" x-cloak class="fixed inset-0 z-50 bg-charcoal/90 flex items-center justify-center p-4" @click.self="open = false">
                <button @click="open = false" class="absolute top-6 end-6 text-cream" aria-label="Close">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                @foreach ($allMedia as $index => $media)
                    <img x-show="active === {{ $index }}" src="{{ $media->getUrl('medium') }}" alt="{{ $project->title }}" class="max-h-[85vh] max-w-full rounded-lg">
                @endforeach
            </div>
        </section>
    @endif

    <section class="bg-stone-100 py-14">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <a href="{{ route('contact') }}" class="inline-flex items-center rounded-md bg-charcoal px-7 py-3 text-sm font-semibold text-cream hover:bg-charcoal-700 transition-colors">
                {{ __('site.common.request_quote') }}
            </a>
        </div>
    </section>
</x-layout>
