<x-layout :title="__('site.contact.thanks_title')" :description="__('site.contact.success_message')" robots="noindex, follow">
    @if ($justSubmitted)
        @push('head')
            {{-- Conversion marker read by the consent-gated analytics loader. --}}
            <meta name="jra-conversion" content="lead_submitted">
        @endpush
    @endif

    <x-page-header :title="__('site.contact.thanks_heading')" :subtitle="session('success') ?? __('site.contact.success_message')" texture="texture-sandstone" />

    <section class="py-16 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-xl border border-sand-200 bg-white p-6 sm:p-8">
            <h2 class="text-section-title font-bold text-charcoal">{{ __('site.contact.thanks_next') }}</h2>
            <x-motif.najdi-band variant="underline" class="mt-3" />
            <ol class="mt-6 space-y-4">
                @foreach (__('site.contact.thanks_steps') as $index => $step)
                    <li class="flex items-start gap-4">
                        <span class="shrink-0 h-9 w-9 rounded-full bg-charcoal text-gold-400 flex items-center justify-center font-bold" aria-hidden="true">{{ $index + 1 }}</span>
                        <span class="pt-1.5 text-stone-700 leading-relaxed rtl:leading-loose">{{ $step }}</span>
                    </li>
                @endforeach
            </ol>

            <div class="mt-8 flex flex-wrap gap-3">
                @if ($settings->whatsapp_url)
                    <x-button :href="$settings->whatsapp_url" data-wa-track target="_blank" rel="noopener noreferrer">
                        <x-social-icon network="whatsapp" class="h-4 w-4" /> {{ __('site.common.whatsapp_us') }}
                    </x-button>
                @endif
                <x-button :href="route('home')" variant="outline">{{ __('site.contact.thanks_back') }}</x-button>
            </div>
        </div>

        <p class="mt-10 text-center text-stone-700">{{ __('site.contact.thanks_browse') }}</p>
        <div class="mt-4 flex flex-wrap justify-center gap-4">
            <a href="{{ route('projects.index') }}" class="link-sweep inline-flex min-h-11 items-center font-semibold text-charcoal">{{ __('site.nav.projects') }}</a>
            <a href="{{ route('stones.index') }}" class="link-sweep inline-flex min-h-11 items-center font-semibold text-charcoal">{{ __('site.nav.stones') }}</a>
            <a href="{{ route('services.index') }}" class="link-sweep inline-flex min-h-11 items-center font-semibold text-charcoal">{{ __('site.nav.services') }}</a>
        </div>
    </section>
</x-layout>
