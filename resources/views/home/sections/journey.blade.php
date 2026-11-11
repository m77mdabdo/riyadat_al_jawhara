{{-- From the quarry to your space: full-bleed chapters. The photo scales and drifts with scroll
     (x-cinema sets --p from 0 to 1 while the chapter crosses the viewport); static for reduced motion. --}}
@php $chapters = \App\Support\HomeSections::journeyItems(); @endphp
@if ($chapters !== [])
<section x-data class="relative bg-charcoal text-cream" aria-labelledby="journey-title">
    <div x-reveal class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-12 sm:pb-16">
        <x-section-heading :title="__('site.home.journey_title')" :subtitle="__('site.home.journey_subtitle')" tone="light" id="journey-title" />
    </div>
    <ol class="list-none">
        @foreach ($chapters as $index => $chapter)
            @php $chapterUrl = $chapter['slot'] !== '' ? \App\Support\SiteImages::url($chapter['slot']) : null; @endphp
            <li x-cinema class="cinema-chapter relative isolate overflow-hidden min-h-[78vh] sm:min-h-[92vh] flex items-end">
                <div class="cinema-media absolute inset-0 -z-10" aria-hidden="true">
                    @if ($chapterUrl)
                        <img src="{{ $chapterUrl }}" srcset="{{ \App\Support\SiteImages::srcset($chapter['slot']) }}" sizes="100vw" alt="" width="1880" height="1253" loading="lazy" decoding="async" class="h-full w-full object-cover saturate-[0.9] contrast-[1.04]">
                    @else
                        <div class="absolute inset-0 bg-charcoal"><x-motif.geo-pattern opacity="0.06" /></div>
                    @endif
                </div>
                <div class="absolute inset-0 -z-10 bg-gradient-to-t from-charcoal via-charcoal/55 to-charcoal/10" aria-hidden="true"></div>
                <div @class(['cinema-copy relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 sm:pb-24 flex', 'justify-end' => $index % 2 === 1])>
                    <div class="max-w-xl">
                        <span class="cinema-index block font-display-en text-gold-400 text-6xl sm:text-8xl leading-none tabular-nums" aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="mt-4 text-3xl sm:text-5xl font-bold break-words">{{ $chapter['title'] }}</h3>
                        <span class="cinema-rule mt-5 block h-px w-24 bg-gold-400" aria-hidden="true"></span>
                        @if ($chapter['description'] !== '')
                            <p class="mt-5 text-base sm:text-lg text-stone-200 leading-prose">{{ $chapter['description'] }}</p>
                        @endif
                    </div>
                </div>
            </li>
        @endforeach
    </ol>
</section>
@endif
