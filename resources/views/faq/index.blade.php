<x-layout :title="__('site.faq.title')" :description="__('site.faq.subtitle')">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-charcoal">{{ __('site.faq.title') }}</h1>
            <p class="mt-3 text-stone-600">{{ __('site.faq.subtitle') }}</p>
        </div>
    </section>

    <section class="py-16 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-3">
            @forelse ($faqs as $faq)
                <div x-data="{ open: false }" class="rounded-lg border border-stone-200 bg-white">
                    <button
                        @click="open = !open"
                        :aria-expanded="open.toString()"
                        class="w-full flex items-center justify-between p-5 text-start"
                    >
                        <span class="font-medium text-charcoal">{{ $faq->question }}</span>
                        <svg class="h-5 w-5 text-stone-500 shrink-0 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak x-transition class="px-5 pb-5 text-sm text-stone-600 leading-relaxed rtl:leading-loose">
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
