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
                    <img src="{{ $project->getFirstMediaUrl('cover', 'medium') }}" alt="{{ $project->title }}" class="h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04]" loading="eager" fetchpriority="high">
                @else
                    <x-image-placeholder class="h-full w-full" />
                @endif
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold text-charcoal">{{ $project->title }}</h1>
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
                    <span><span class="font-semibold text-charcoal">{{ __('site.projects.completed_at') }}:</span> <bdi>{{ $project->completed_at->format('Y-m-d') }}</bdi></span>
                @endif
            </div>
            <p class="mt-6 text-stone-600 leading-relaxed rtl:leading-loose">{{ $project->description }}</p>
        </div>
    </section>

    @if ($project->getFirstMediaUrl('before') && $project->getFirstMediaUrl('after'))
        <section class="py-16 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8 flex-wrap gap-2">
                <h2 class="text-2xl sm:text-3xl font-bold text-charcoal">{{ __('site.projects.before') }} / {{ __('site.projects.after') }}</h2>
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
                @keydown.arrow-left="pos = Math.max(0, pos - 5)"
                @keydown.arrow-right="pos = Math.min(100, pos + 5)"
                role="slider"
                tabindex="0"
                :aria-valuenow="Math.round(pos)"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-label="{{ __('site.projects.before_after_slider') }}"
                class="relative aspect-[16/10] sm:aspect-[21/9] rounded-xl overflow-hidden bg-stone-200 select-none touch-none cursor-ew-resize focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-charcoal focus-visible:ring-offset-2"
            >
                <img src="{{ $project->getFirstMediaUrl('after', 'medium') }}" alt="{{ __('site.projects.after') }}" loading="lazy" draggable="false" class="absolute inset-0 h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04] pointer-events-none">

                <img src="{{ $project->getFirstMediaUrl('before', 'medium') }}" alt="{{ __('site.projects.before') }}" loading="lazy" draggable="false" :style="`clip-path: inset(0 ${100 - pos}% 0 0)`" class="absolute inset-0 h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04] pointer-events-none">

                <span class="absolute top-4 left-4 rounded-full bg-charcoal/70 text-cream text-xs font-semibold px-3 py-1 pointer-events-none">{{ __('site.projects.before') }}</span>
                <span class="absolute top-4 right-4 rounded-full bg-charcoal/70 text-cream text-xs font-semibold px-3 py-1 pointer-events-none">{{ __('site.projects.after') }}</span>

                <div class="absolute inset-y-0 w-0.5 bg-cream pointer-events-none" :style="`left: ${pos}%`">
                    <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 h-10 w-10 rounded-full bg-cream shadow-lg flex items-center justify-center">
                        <svg class="h-5 w-5 text-charcoal" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l-4 4 4 4m8-12l4 4-4 4" /></svg>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($allMedia->isNotEmpty())
        <section class="py-16 bg-white" x-data="{ open: false, active: 0, count: {{ $allMedia->count() }}, touchX: 0, next() { this.active = (this.active + 1) % this.count }, prev() { this.active = (this.active - 1 + this.count) % this.count } }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-charcoal mb-8">{{ __('site.projects.gallery') }}</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($allMedia as $index => $media)
                        <button @click="active = {{ $index }}; open = true" class="aspect-square rounded-lg overflow-hidden bg-stone-200" aria-label="{{ __('site.common.view_image') }}: {{ $project->title }}">
                            <img src="{{ $media->getUrl('thumb') }}" alt="{{ $project->title }}" loading="lazy" class="h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04] hover:scale-105 transition-transform duration-300">
                        </button>
                    @endforeach
                </div>
            </div>

            <div x-show="open" x-cloak x-transition.opacity.duration.200ms class="fixed inset-0 z-50 bg-charcoal/90 flex items-center justify-center p-4"
                 @click.self="open = false"
                 @keydown.escape.window="open = false"
                 @keydown.arrow-right.window="next()"
                 @keydown.arrow-left.window="prev()"
                 @touchstart="touchX = $event.changedTouches[0].screenX"
                 @touchend="const dx = $event.changedTouches[0].screenX - touchX; if (Math.abs(dx) > 40) { dx < 0 ? next() : prev() }">
                <button @click="open = false" class="absolute top-6 end-6 text-cream" aria-label="{{ __('site.common.close') }}">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                @if ($allMedia->count() > 1)
                    <button @click.stop="prev()" class="absolute start-2 sm:start-4 top-1/2 -translate-y-1/2 h-11 w-11 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-cream" aria-label="{{ __('site.common.previous_image') }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button @click.stop="next()" class="absolute end-2 sm:end-4 top-1/2 -translate-y-1/2 h-11 w-11 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-cream" aria-label="{{ __('site.common.next_image') }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                @endif
                @foreach ($allMedia as $index => $media)
                    <img x-show="active === {{ $index }}" src="{{ $media->getUrl('medium') }}" alt="{{ $project->title }}" loading="lazy" class="max-h-[85vh] max-w-full rounded-lg saturate-[0.92] contrast-[1.03] sepia-[0.04]">
                @endforeach
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
