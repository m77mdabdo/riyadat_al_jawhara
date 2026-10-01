{{-- Why choose us --}}
<section class="relative overflow-hidden bg-sand-100">
    <x-motif.geo-pattern />
    <div x-reveal class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <x-section-heading :title="__('site.home.why_choose_us')" :subtitle="__('site.home.why_choose_us_subtitle')" class="mb-12" />
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach (\App\Support\HomeSections::whyItems() as $index => $item)
                <div x-reveal style="--reveal-delay: {{ $index * 90 }}ms" class="group rounded-xl border border-sand-200 bg-cream p-6 transition-all hover:-translate-y-1 hover:shadow-lg">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-charcoal text-gold-400">
                        <x-dynamic-component :component="$item['icon']" class="h-6 w-6" aria-hidden="true" />
                    </span>
                    <h3 class="mt-4 font-semibold text-charcoal">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm text-stone-700 leading-prose">{{ $item['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
