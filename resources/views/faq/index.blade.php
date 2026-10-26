<x-layout :title="__('site.faq.title')" :description="__('site.faq.subtitle')" :breadcrumbs="[['name' => __('site.faq.title'), 'url' => route('faq')]]">
    @if ($faqs->isNotEmpty())
        @push('head')
            <script type="application/ld+json">{!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqs->map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => $faq->question,
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq->answer],
                ])->values()->all(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
        @endpush
    @endif

    <x-page-header :title="__('site.faq.title')" :subtitle="__('site.faq.subtitle')" texture="texture-quartz" />

    <section class="py-16 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-3">
            @forelse ($faqs as $faq)
                <div id="faq-{{ $faq->id }}" x-data="{ open: window.location.hash === '#faq-{{ $faq->id }}' }" class="scroll-mt-24 rounded-lg border border-stone-200 bg-white">
                    <button
                        type="button"
                        @click="open = !open"
                        :aria-expanded="open.toString()"
                        aria-controls="faq-answer-{{ $faq->id }}"
                        class="w-full flex items-center justify-between gap-4 p-5 text-start"
                    >
                        <span class="font-medium text-charcoal">{{ $faq->question }}</span>
                        <svg class="h-5 w-5 text-stone-500 shrink-0 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="faq-answer-{{ $faq->id }}" x-show="open" x-cloak x-transition class="px-5 pb-5 text-sm text-stone-600 leading-relaxed rtl:leading-loose">
                        {{ $faq->answer }}
                    </div>
                </div>
            @empty
                <p class="text-stone-600 text-center py-12">{{ __('site.faq.no_faqs_yet') }}</p>
            @endforelse
        </div>

        <div class="mt-12 text-center">
            <p class="text-stone-600 mb-4">{{ __('site.faq.still_have_questions') }}</p>
            <x-button :href="route('contact')">{{ __('site.common.request_quote') }}</x-button>
        </div>
    </section>
</x-layout>
