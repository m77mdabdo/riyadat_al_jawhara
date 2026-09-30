<x-mail::message :preheader="$preheader">
# {{ $greeting }}

{{ __('site.mail.auto_reply.intro') }}

@if ($topic)
{{ __('site.mail.auto_reply.topic') }} **{{ $topic }}**
@endif

{{ __('site.mail.auto_reply.reach_us') }}

<x-mail::buttons>
@if ($settings?->whatsapp_url)
<x-mail::buttons.item :url="$settings->whatsapp_url">{{ __('site.mail.auto_reply.whatsapp_us') }}</x-mail::buttons.item>
@endif
@if ($settings?->phone_href)
<x-mail::buttons.item :url="$settings->phone_href" color="secondary">{{ __('site.mail.auto_reply.call_us') }}</x-mail::buttons.item>
@endif
</x-mail::buttons>

{{ __('site.mail.regards') }}<br>
{{ __('site.meta.site_name') }}

<x-slot:subcopy>
{{ __('site.mail.auto_reply.note') }}
</x-slot:subcopy>
</x-mail::message>
