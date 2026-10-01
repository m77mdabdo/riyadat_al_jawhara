{{-- Featured Projects (mashrabiya-framed) --}}
@if ($featuredProjects->isNotEmpty())
    <section class="py-20">
        <div x-reveal class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-12">
                <x-section-heading :title="__('site.home.featured_projects')" align="start" />
                <x-view-all-link :href="route('projects.index')" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($featuredProjects as $project)
                    <x-motif.mashrabiya-frame x-reveal style="--reveal-delay: {{ $loop->index * 90 }}ms">
                        <x-cards.project :project="$project" />
                    </x-motif.mashrabiya-frame>
                @endforeach
            </div>
        </div>
    </section>
@endif
