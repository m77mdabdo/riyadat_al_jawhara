{{-- Testimonials --}}
@if ($testimonials->isNotEmpty())
    <section x-reveal class="relative py-20 bg-charcoal text-cream overflow-hidden">
        <img src="{{ \App\Support\SiteImages::url('texture-granite', asset('images/textures/stone-texture.webp')) }}" @if ($srcset = \App\Support\SiteImages::srcset('texture-granite')) srcset="{{ $srcset }}" sizes="100vw" @endif alt="" aria-hidden="true" width="1280" height="853" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover opacity-10">
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-section-heading :title="__('site.home.testimonials')" tone="light" class="mb-12" />
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" x-data>
                @foreach ($testimonials as $testimonial)
                    <div class="rounded-xl bg-white/5 border border-white/10 p-6">
                        <div class="flex gap-1 mb-3 text-stone-300">
                            @for ($i = 0; $i < 5; $i++)
                                <svg aria-hidden="true" class="h-4 w-4 {{ $i < $testimonial->rating ? 'text-gold-400' : 'text-stone-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M10 15l-5.9 3.1 1.1-6.6L.4 6.9l6.6-1L10 0l3 5.9 6.6 1-4.8 4.6 1.1 6.6z"/></svg>
                            @endfor
                        </div>
                        <p class="text-stone-200 text-sm leading-prose">&ldquo;{{ $testimonial->comment }}&rdquo;</p>
                        <div class="mt-4 flex items-center gap-3">
                            <div class="h-9 w-9 rounded-full overflow-hidden shrink-0">
                                <x-media-image :model="$testimonial" collection="client_photo" conversion="thumb" alt="" :responsive="false" :width="200" :height="200" :placeholder-icon="false" class="h-full w-full" />
                            </div>
                            <p class="font-semibold text-sm">{{ $testimonial->client_name }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
