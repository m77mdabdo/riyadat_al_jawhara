<x-layout :title="__('site.projects.title')">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-charcoal">{{ __('site.projects.title') }}</h1>
        </div>
    </section>

    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="GET" class="flex flex-wrap gap-4 mb-10">
            <select name="service" onchange="this.form.submit()" class="rounded-md border-stone-300 text-sm text-charcoal focus:border-charcoal focus:ring-charcoal">
                <option value="">{{ __('site.projects.service') }}: {{ __('site.common.all') }}</option>
                @foreach ($services as $service)
                    <option value="{{ $service->slug }}" @selected($activeService === $service->slug)>{{ $service->name }}</option>
                @endforeach
            </select>
            <select name="stone_type" onchange="this.form.submit()" class="rounded-md border-stone-300 text-sm text-charcoal focus:border-charcoal focus:ring-charcoal">
                <option value="">{{ __('site.projects.stone_type') }}: {{ __('site.common.all') }}</option>
                @foreach ($stoneTypes as $stoneType)
                    <option value="{{ $stoneType->slug }}" @selected($activeStoneType === $stoneType->slug)>{{ $stoneType->name }}</option>
                @endforeach
            </select>
            @if ($activeService || $activeStoneType)
                <a href="{{ route('projects.index') }}" class="text-sm font-medium text-stone-600 hover:text-charcoal self-center">{{ __('site.common.all') }} &times;</a>
            @endif
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($projects as $project)
                <a href="{{ route('projects.show', $project) }}" class="group block rounded-xl overflow-hidden border border-stone-200 bg-white">
                    <div class="aspect-[4/3] bg-stone-200 overflow-hidden relative">
                        @if ($project->getFirstMediaUrl('cover'))
                            <img src="{{ $project->getFirstMediaUrl('cover', 'medium') }}" alt="{{ $project->title }}" loading="lazy" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @endif
                        @if ($project->is_featured)
                            <span class="absolute top-3 start-3 bg-charcoal text-cream text-xs font-semibold px-3 py-1 rounded-full">{{ __('site.common.featured') }}</span>
                        @endif
                    </div>
                    <div class="p-5">
                        <h3 class="font-semibold text-charcoal">{{ $project->title }}</h3>
                        <p class="text-sm text-stone-600 mt-1">{{ $project->location }}</p>
                    </div>
                </a>
            @empty
                <p class="text-stone-600 col-span-full text-center">{{ __('site.common.no_results') }}</p>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $projects->links() }}
        </div>
    </section>
</x-layout>
