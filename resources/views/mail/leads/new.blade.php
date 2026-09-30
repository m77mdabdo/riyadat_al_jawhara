<x-mail::message :preheader="$preheader">
# {{ __('site.mail.lead.heading') }}

<x-mail::chip>{{ __('site.mail.lead.status') }}</x-mail::chip>

{{ __('site.mail.lead.intro') }}

<x-mail::details :rows="$rows" />

@if (filled($lead->message))
**{{ __('site.mail.lead.message') }}**

<x-mail::quote>{{ $lead->message }}</x-mail::quote>
@endif

<x-mail::buttons>
<x-mail::buttons.item :url="$actionUrl">{{ __('site.mail.lead.open_admin') }}</x-mail::buttons.item>
</x-mail::buttons>

<x-mail::buttons>
@if ($whatsappUrl)
<x-mail::buttons.item :url="$whatsappUrl" color="secondary">{{ __('site.mail.lead.whatsapp_customer') }}</x-mail::buttons.item>
@endif
@if ($telUrl)
<x-mail::buttons.item :url="$telUrl" color="secondary">{{ __('site.mail.lead.call') }}</x-mail::buttons.item>
@endif
@if ($mailtoUrl)
<x-mail::buttons.item :url="$mailtoUrl" color="secondary">{{ __('site.mail.lead.reply') }}</x-mail::buttons.item>
@endif
</x-mail::buttons>

@if ($contextUrl)
{{ __('site.mail.lead.context') }} [{{ $contextUrl }}]({{ $contextUrl }})
@endif
</x-mail::message>
