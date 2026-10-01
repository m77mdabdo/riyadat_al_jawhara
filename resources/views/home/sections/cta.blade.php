{{-- CTA --}}
<section x-reveal class="relative bg-sand-100 py-20 overflow-hidden">
    @php $ctaBackground = \App\Support\HomeSections::ctaBackground(); @endphp
    <img src="{{ \App\Support\SiteImages::url($ctaBackground, asset('images/textures/marble-texture.webp')) }}" @if ($srcset = \App\Support\SiteImages::srcset($ctaBackground)) srcset="{{ $srcset }}" sizes="100vw" @endif alt="" aria-hidden="true" width="1280" height="853" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover opacity-20">
    <div class="absolute inset-0 bg-cream/60" aria-hidden="true"></div>
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <x-section-heading :title="__('site.home.cta_title')" :subtitle="__('site.home.cta_subtitle')" />
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <x-button :href="route('contact')">{{ __('site.common.request_quote') }}</x-button>
            @if ($settings->whatsapp_url)
                <x-button :href="$settings->whatsapp_url" variant="outline" data-wa-track target="_blank" rel="noopener noreferrer">
                    <x-social-icon network="whatsapp" class="h-4 w-4" /> {{ __('site.common.whatsapp_us') }}
                </x-button>
            @endif
        </div>
    </div>
</section>
