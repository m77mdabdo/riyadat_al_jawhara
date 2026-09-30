@php
    $locale = app()->getLocale();
    $rtl = $locale === 'ar';
@endphp
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
<title>{{ __('site.meta.site_name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
{{-- Light and dark both declared: our colours stay readable when a client inverts them. --}}
<meta name="color-scheme" content="light dark">
<meta name="supported-color-schemes" content="light dark">
<style>
@media only screen and (max-width: 600px) {
.inner-body, .footer { width: 100% !important; }
.content-cell { padding: 22px !important; }
.details th, .details td { display: block !important; width: 100% !important; padding: 4px 0 !important; border: 0 !important; }
.details td { padding-bottom: 10px !important; border-bottom: 1px solid #F3EBDD !important; }
}
@media only screen and (max-width: 500px) {
.button-cell { display: block !important; width: 100% !important; }
}
@media (prefers-color-scheme: dark) {
.inner-body { background-color: #FFFFFF !important; }
}
</style>
{!! $head ?? '' !!}
</head>
<body class="{{ $rtl ? 'rtl' : 'ltr' }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
@if (! empty($preheader))
<div class="preheader">{{ $preheader }}</div>
@endif

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<tr>
<td align="center">
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
{!! $header ?? '' !!}

<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
<table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0" role="presentation" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<tr>
<td class="najdi" aria-hidden="true">&#9650;&#9650;&#9650;&#9650;&#9650;&#9650;&#9650;</td>
</tr>
<!-- Body content -->
<tr>
<td class="content-cell" align="{{ $rtl ? 'right' : 'left' }}">
{!! Illuminate\Mail\Markdown::parse($slot) !!}

{!! $subcopy ?? '' !!}
</td>
</tr>
</table>
</td>
</tr>

{!! $footer ?? '' !!}
</table>
</td>
</tr>
</table>
</body>
</html>
