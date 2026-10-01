{{-- FAQ --}}
@if ($faqs->isNotEmpty())
    <section x-reveal class="py-20 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-section-heading :title="__('site.home.faq')" class="mb-12" />
        <div class="space-y-3">
            @foreach ($faqs as $faq)
                <div x-data="{ open: false }" class="rounded-lg border border-stone-200 bg-white">
                    <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="home-faq-{{ $faq->id }}" class="w-full flex items-center justify-between gap-4 p-5 text-start">
                        <span class="font-medium text-charcoal">{{ $faq->question }}</span>
                        <svg class="h-5 w-5 text-stone-500 shrink-0 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="home-faq-{{ $faq->id }}" x-show="open" x-cloak x-transition class="px-5 pb-5 text-sm text-stone-600 leading-prose">
                        {{ $faq->answer }}
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-8 text-center">
            <x-view-all-link :href="route('faq')" />
        </div>
    </section>
@endif
