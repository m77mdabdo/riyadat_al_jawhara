<x-layout :title="__('site.legal.privacy_policy_title')">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-charcoal">{{ __('site.legal.privacy_policy_title') }}</h1>
        </div>
    </section>

    <section class="py-16 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        @if ($settings->privacy_policy)
            <div class="prose prose-stone max-w-none rtl:leading-loose">
                {!! $settings->privacy_policy !!}
            </div>
        @else
            <p class="text-stone-600">{{ __('site.legal.content_missing') }}</p>
        @endif
    </section>
</x-layout>
