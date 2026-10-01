<x-layout page="terms" :title="__('site.legal.terms_title')" :description="$settings->terms" :breadcrumbs="[['name' => __('site.legal.terms_title'), 'url' => route('legal.terms')]]">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-page-title font-bold text-charcoal">{{ __('site.legal.terms_title') }}</h1>
            @if (__('site.legal.terms_subtitle'))
                <p class="mt-4 text-stone-700">{{ __('site.legal.terms_subtitle') }}</p>
            @endif
        </div>
    </section>

    <section class="py-16 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        @if ($settings->terms)
            <div class="prose prose-stone max-w-none rtl:leading-prose">
                {!! str($settings->terms)->sanitizeHtml() !!}
            </div>
        @else
            <p class="text-stone-600">{{ __('site.legal.content_missing') }}</p>
        @endif
    </section>
</x-layout>
