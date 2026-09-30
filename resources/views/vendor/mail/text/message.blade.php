@props(['preheader' => null])
@php
    $s = $settings ?? null;
@endphp
<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="route('home')">
            {{ __('site.meta.site_name') }}
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
{{ __('site.meta.site_name') }}
{{ implode(' · ', array_filter([$s?->phone, $s?->whatsapp_url, $s?->email])) }}
{{ $s?->address }}
© {{ date('Y') }} {{ __('site.meta.site_name') }}
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
