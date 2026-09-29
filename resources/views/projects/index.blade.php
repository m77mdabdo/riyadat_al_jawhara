<x-layout :title="__('site.projects.title')">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-page-title font-bold text-charcoal">{{ __('site.projects.title') }}</h1>
        </div>
    </section>

    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="GET" class="flex flex-wrap gap-3 sm:gap-4 mb-10">
            <select name="service" onchange="this.form.submit()" aria-label="{{ __('site.projects.service') }}" class="w-full sm:w-auto min-h-11 rounded-md border-stone-300 px-3 py-2.5 text-base sm:text-sm text-charcoal focus:border-charcoal focus:ring-charcoal">
                <option value="">{{ __('site.projects.service') }}: {{ __('site.common.all') }}</option>
                @foreach ($services as $service)
                    <option value="{{ $service->slug }}" @selected($activeService === $service->slug)>{{ $service->name }}</option>
                @endforeach
            </select>
            <select name="stone_type" onchange="this.form.submit()" aria-label="{{ __('site.projects.stone_type') }}" class="w-full sm:w-auto min-h-11 rounded-md border-stone-300 px-3 py-2.5 text-base sm:text-sm text-charcoal focus:border-charcoal focus:ring-charcoal">
                <option value="">{{ __('site.projects.stone_type') }}: {{ __('site.common.all') }}</option>
                @foreach ($stoneTypes as $stoneType)
                    <option value="{{ $stoneType->slug }}" @selected($activeStoneType === $stoneType->slug)>{{ $stoneType->name }}</option>
                @endforeach
            </select>
            @if ($sectors->isNotEmpty())
                <select name="sector" onchange="this.form.submit()" aria-label="{{ __('site.projects.sector') }}" class="w-full sm:w-auto min-h-11 rounded-md border-stone-300 px-3 py-2.5 text-base sm:text-sm text-charcoal focus:border-charcoal focus:ring-charcoal">
                    <option value="">{{ __('site.projects.sector') }}: {{ __('site.common.all') }}</option>
                    @foreach ($sectors as $sectorOption)
                        <option value="{{ $sectorOption }}" @selected($activeSector === $sectorOption)>{{ __('site.projects.sectors.'.$sectorOption) }}</option>
                    @endforeach
                </select>
            @endif
            @if ($activeService || $activeStoneType || $activeSector)
                <a href="{{ route('projects.index') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-stone-600 hover:text-charcoal">{{ __('site.common.all') }} &times;</a>
            @endif
        </form>

        <div x-reveal class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($projects as $project)
                <x-cards.project :project="$project" heading-tag="h2" :show-featured="true" />
            @empty
                <p class="text-stone-600 col-span-full text-center py-12">{{ __('site.common.no_results') }}</p>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $projects->links('pagination::tailwind') }}
        </div>
    </section>
</x-layout>
