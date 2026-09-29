<x-layout :title="$project->title" :description="$project->description" :image="$project->coverImageUrl()" :breadcrumbs="[['name' => __('site.projects.title'), 'url' => route('projects.index')], ['name' => $project->title, 'url' => route('projects.show', $project)]]">
    @php
        $hasComparison = $project->getFirstMedia('before') && $project->getFirstMedia('after');
        // Before/after photos already appear in the comparison slider; only add
        // them to the gallery when the slider can't be shown (one of the two missing).
        $galleryMedia = $hasComparison
            ? $project->getMedia('gallery')
            : $project->getMedia('gallery')->merge($project->getMedia('before'))->merge($project->getMedia('after'));
    @endphp

    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="aspect-[16/9] rounded-xl bg-stone-200 overflow-hidden mb-8">
                <x-media-image :model="$project" :alt="$project->title" :eager="true" class="h-full w-full" />
            </div>
            <h1 class="text-page-title font-bold text-charcoal">{{ $project->title }}</h1>
            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-stone-600">
                @if ($project->location)
                    <span><span class="font-semibold text-charcoal">{{ __('site.projects.location') }}:</span> {{ $project->location }}</span>
                @endif
                @if ($project->service)
                    <span><span class="font-semibold text-charcoal">{{ __('site.projects.service') }}:</span> {{ $project->service->name }}</span>
                @endif
                @if ($project->city)
                    <span><span class="font-semibold text-charcoal">{{ __('site.projects.city') }}:</span> {{ $project->city }}</span>
                @endif
                @if ($project->sector && in_array($project->sector, \App\Models\Project::SECTORS, true))
                    <span><span class="font-semibold text-charcoal">{{ __('site.projects.sector') }}:</span> {{ __('site.projects.sectors.'.$project->sector) }}</span>
                @endif
                @if ($project->area_m2)
                    <span><span class="font-semibold text-charcoal">{{ __('site.projects.area') }}:</span> <bdi>{{ __('site.projects.area_value', ['area' => rtrim(rtrim(number_format((float) $project->area_m2, 2), '0'), '.')]) }}</bdi></span>
                @endif
                @if ($project->completed_at)
                    <span><span class="font-semibold text-charcoal">{{ __('site.projects.completed_at') }}:</span> <bdi>{{ $project->completed_at->format('Y-m-d') }}</bdi></span>
                @endif
            </div>
            @php
                $materials = $project->allStoneTypes();
            @endphp
            @if ($materials->isNotEmpty())
                <div class="mt-5">
                    <p class="text-sm font-semibold text-charcoal mb-2">{{ __('site.projects.materials') }}</p>
                    <ul class="flex flex-wrap gap-2">
                        @foreach ($materials as $material)
                            <li>
                                <a href="{{ route('stones.show', $material) }}" class="inline-flex min-h-11 items-center rounded-full bg-stone-100 px-4 py-2 text-sm text-charcoal hover:bg-stone-200 transition-colors">{{ $material->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <p class="mt-6 text-stone-600 leading-relaxed rtl:leading-loose">{{ $project->description }}</p>
        </div>
    </section>

    @if ($hasComparison)
        <section class="py-16 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8 flex-wrap gap-2">
                <h2 class="text-section-title font-bold text-charcoal">{{ __('site.projects.before') }} / {{ __('site.projects.after') }}</h2>
                <p class="text-sm text-stone-500">{{ __('site.projects.drag_to_compare') }}</p>
            </div>

            <div
                x-data="{
                    pos: 50,
                    dragging: false,
                    setFromClientX(clientX) {
                        const rect = $el.getBoundingClientRect();
                        this.pos = Math.min(100, Math.max(0, ((clientX - rect.left) / rect.width) * 100));
                    },
                }"
                @mousedown="dragging = true; setFromClientX($event.clientX)"
                @mousemove.window="dragging && setFromClientX($event.clientX)"
                @mouseup.window="dragging = false"
                @touchstart="dragging = true; setFromClientX($event.touches[0].clientX)"
                @touchmove="dragging && setFromClientX($event.touches[0].clientX)"
                @touchend="dragging = false"
                @keydown.arrow-left.prevent="pos = Math.max(0, pos - 5)"
                @keydown.arrow-right.prevent="pos = Math.min(100, pos + 5)"
                @keydown.home.prevent="pos = 0"
                @keydown.end.prevent="pos = 100"
                {{-- Physical, not logical: "before" is always the left part of the photo,
                     so the handle, labels and arrow keys stay left-to-right in Arabic too. --}}
                dir="ltr"
                role="slider"
                tabindex="0"
                :aria-valuenow="Math.round(pos)"
                :aria-valuetext="`${Math.round(pos)}% {{ __('site.projects.before') }}`"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-label="{{ __('site.projects.before_after_slider') }}"
                class="relative aspect-[16/10] sm:aspect-[21/9] rounded-xl overflow-hidden bg-stone-200 select-none touch-none cursor-ew-resize focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-charcoal focus-visible:ring-offset-2"
            >
                <img src="{{ $project->getFirstMediaUrl('after', 'medium') }}" alt="{{ __('site.projects.after') }}: {{ $project->title }}" width="1200" height="700" loading="lazy" decoding="async" draggable="false" class="absolute inset-0 h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04] pointer-events-none">

                <img src="{{ $project->getFirstMediaUrl('before', 'medium') }}" alt="{{ __('site.projects.before') }}: {{ $project->title }}" width="1200" height="700" loading="lazy" decoding="async" draggable="false" :style="`clip-path: inset(0 ${100 - pos}% 0 0)`" class="absolute inset-0 h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04] pointer-events-none">

                <span class="absolute top-4 left-4 rounded-full bg-charcoal/70 text-cream text-sm sm:text-xs font-semibold px-3 py-1 pointer-events-none">{{ __('site.projects.before') }}</span>
                <span class="absolute top-4 right-4 rounded-full bg-charcoal/70 text-cream text-sm sm:text-xs font-semibold px-3 py-1 pointer-events-none">{{ __('site.projects.after') }}</span>

                <div class="absolute inset-y-0 w-0.5 bg-cream pointer-events-none" :style="`left: ${pos}%`">
                    <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 h-10 w-10 rounded-full bg-cream shadow-lg flex items-center justify-center">
                        <svg class="h-5 w-5 text-charcoal" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l-4 4 4 4m8-12l4 4-4 4" /></svg>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($galleryMedia->isNotEmpty())
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-section-title font-bold text-charcoal mb-8">{{ __('site.projects.gallery') }}</h2>
                <x-lightbox :media="$galleryMedia" :alt="$project->title" />
            </div>
        </section>
    @endif

    <section class="bg-stone-100 py-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <x-button :href="route('contact', ['project' => $project->slug])">
                {{ __('site.common.request_quote') }}
            </x-button>
        </div>
    </section>
</x-layout>
