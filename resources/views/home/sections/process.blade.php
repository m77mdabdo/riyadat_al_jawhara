{{-- How We Work: Najdi-frieze connectors --}}
<section class="relative overflow-hidden bg-sand-100">
    <x-motif.geo-pattern />
    <div x-reveal class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <x-section-heading :title="__('site.process.title')" :subtitle="__('site.process.subtitle')" class="mb-16" />
        <div class="relative">
            <x-motif.najdi-band class="hidden lg:flex absolute inset-x-[10%] top-3 !py-0" tone="sand" />
            <span class="lg:hidden absolute top-0 bottom-0 start-6 w-px bg-sand-300" aria-hidden="true"></span>
        <ol class="relative grid grid-cols-1 lg:grid-cols-5 gap-10 lg:gap-6">
            @foreach (\App\Support\HomeSections::processSteps() as $index => $step)
                <li x-reveal style="--reveal-delay: {{ $index * 110 }}ms" class="relative flex items-start lg:flex-col lg:items-center gap-4 lg:gap-0 lg:text-center">
                    <span class="relative z-10 shrink-0 h-12 w-12 rounded-full bg-charcoal text-gold-400 ring-4 ring-sand-100 flex items-center justify-center font-bold" aria-hidden="true">{{ $index + 1 }}</span>
                    <div class="lg:mt-5">
                        <h3 class="font-semibold text-charcoal">{{ $step['title'] }}</h3>
                        <p class="mt-1 text-sm text-stone-700 leading-prose">{{ $step['description'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
        </div>
    </div>
</section>
