@props(['preheader' => null])
@php
    $s = $settings ?? null;
    $contact = array_filter([
        $s?->phone ? '<a href="'.e($s->phone_href).'" dir="ltr">'.e($s->phone).'</a>' : null,
        $s?->whatsapp_url ? '<a href="'.e($s->whatsapp_url).'">'.e(__('site.mail.whatsapp')).'</a>' : null,
        $s?->email ? '<a href="mailto:'.e($s->email).'" dir="ltr">'.e($s->email).'</a>' : null,
    ]);
    $socials = collect(['facebook_url' => 'Facebook', 'instagram_url' => 'Instagram', 'x_url' => 'X', 'linkedin_url' => 'LinkedIn', 'youtube_url' => 'YouTube', 'tiktok_url' => 'TikTok', 'snapchat_url' => 'Snapchat'])
        ->filter(fn ($label, $field) => filled($s?->{$field}))
        ->map(fn ($label, $field) => '<a href="'.e($s->{$field}).'">'.e($label).'</a>');
@endphp
<x-mail::layout :preheader="$preheader">
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="route('home')" />
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer: the company's contact channels from Settings --}}
<x-slot:footer>
<x-mail::footer>
**{{ __('site.meta.site_name') }}**<br>
@if ($contact){!! implode(' · ', $contact) !!}<br>@endif
@if (filled($s?->address)){{ $s->address }}<br>@endif
@if ($socials->isNotEmpty()){!! $socials->implode(' · ') !!}<br>@endif
© {{ date('Y') }} {{ __('site.meta.site_name') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
