{{-- "View all →" link under a home section (arrow mirrors in RTL). --}}
@props(['href'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'link-sweep inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-charcoal']) }}>
    {{ __('site.common.view_all') }} <x-heroicon-o-arrow-right class="h-4 w-4 rtl:rotate-180" aria-hidden="true" />
</a>
