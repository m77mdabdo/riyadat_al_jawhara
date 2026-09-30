<x-mail::message :preheader="$preheader">
# {{ __('site.mail.comment.heading') }}

{{ $intro }}

<x-mail::button :url="$actionUrl">
{{ $actionText }}
</x-mail::button>
</x-mail::message>
