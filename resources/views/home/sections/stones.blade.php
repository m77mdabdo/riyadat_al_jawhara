{{-- Stone Collection --}}
@if ($stoneTypes->isNotEmpty())
    <section x-reveal class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-12">
                <x-section-heading :title="__('site.home.stone_collection')" align="start" />
                <x-view-all-link :href="route('stones.index')" />
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach ($stoneTypes as $stoneType)
                    <x-cards.stone :stone-type="$stoneType" />
                @endforeach
            </div>
        </div>
    </section>
@endif
