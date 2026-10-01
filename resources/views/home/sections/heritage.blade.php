{{-- Heritage in stone (inspiration imagery, clearly labelled) --}}
<section class="py-20">
    <div x-reveal class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-section-heading :title="__('site.home.heritage_title')" :subtitle="__('site.home.heritage_subtitle')" class="mb-12" />
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach (\App\Support\HomeSections::heritageItems() as $item)
                @php $heritageUrl = $item['slot'] !== '' ? \App\Support\SiteImages::url($item['slot']) : null; @endphp
                <figure x-reveal style="--reveal-delay: {{ $loop->index * 90 }}ms" class="group overflow-hidden rounded-xl border border-sand-200 bg-cream">
                    <div class="relative aspect-[4/3] overflow-hidden bg-sand-100">
                        @if ($heritageUrl)
                            <img src="{{ $heritageUrl }}" srcset="{{ \App\Support\SiteImages::srcset($item['slot']) }}" sizes="(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw" alt="{{ $item['name'] }}: {{ $item['caption'] }}" width="1880" height="1253" loading="lazy" decoding="async" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                        @else
                            {{-- No verified photo of this place: an honest pattern card instead. --}}
                            <div class="absolute inset-0 flex items-center justify-center bg-sand-200">
                                <x-motif.geo-pattern opacity="0.05" />
                                <x-motif.najdi-band class="w-3/4" tone="gold" />
                            </div>
                        @endif
                    </div>
                    <figcaption class="p-5">
                        <p class="font-semibold text-charcoal">{{ $item['name'] }}</p>
                        <p class="mt-1 text-sm text-stone-700">{{ $item['caption'] }}</p>
                    </figcaption>
                </figure>
            @endforeach
        </div>
        <p class="mt-6 text-center text-sm text-stone-600">{{ __('site.home.heritage_note') }}</p>
    </div>
</section>
