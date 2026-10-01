<x-layout page="projects" :title="__('site.projects.title')" :description="__('site.projects.meta_description')" :breadcrumbs="[['name' => __('site.projects.title'), 'url' => route('projects.index')]]">
    <x-page-header page="projects" :title="__('site.projects.title')" :subtitle="__('site.projects.meta_description')" texture="texture-sandstone" />

    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="GET" class="flex flex-wrap gap-3 sm:gap-4 mb-10">
            <x-filter-select name="service" :label="__('site.projects.service')" :options="$services->pluck('name', 'slug')" :selected="$activeService" />
            <x-filter-select name="stone_type" :label="__('site.projects.stone_type')" :options="$stoneTypes->pluck('name', 'slug')" :selected="$activeStoneType" />
            @if ($sectors->isNotEmpty())
                <x-filter-select name="sector" :label="__('site.projects.sector')" :options="$sectors->mapWithKeys(fn ($s) => [$s => \App\Enums\ProjectSector::labelFor($s)])" :selected="$activeSector" />
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
