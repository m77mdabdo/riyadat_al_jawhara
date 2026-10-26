@props(['project', 'headingTag' => 'h3', 'showLocation' => true, 'showFeatured' => false])

<a href="{{ route('projects.show', $project) }}" {{ $attributes->class('group block rounded-xl overflow-hidden border border-stone-200 bg-white hover:border-sand-300 hover:shadow-lg hover:-translate-y-1 transition-all duration-300') }}>
    <div class="aspect-[4/3] bg-stone-200 overflow-hidden relative">
        <x-media-image :model="$project" :alt="$project->title" class="h-full w-full" img-class="group-hover:scale-105 transition-transform duration-700" />
        @if ($showFeatured && $project->is_featured)
            <span class="absolute top-3 start-3 bg-charcoal text-cream text-sm sm:text-xs font-semibold px-3 py-1 rounded-full">{{ __('site.common.featured') }}</span>
        @endif
    </div>
    <div class="p-5">
        <{{ $headingTag }} class="font-semibold text-charcoal break-words"><span class="link-sweep">{{ $project->title }}</span></{{ $headingTag }}>
        @if ($showLocation && $project->location)
            <p class="text-sm text-stone-600 mt-1">{{ $project->location }}</p>
        @endif
    </div>
</a>
