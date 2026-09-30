<x-mail::message :preheader="$preheader">
# {{ __('site.mail.digest.heading') }}

{{ __('site.mail.digest.intro') }}

<x-mail::details :rows="$rows" />

<x-mail::button :url="$actionUrl">
{{ $actionText }}
</x-mail::button>
</x-mail::message>
