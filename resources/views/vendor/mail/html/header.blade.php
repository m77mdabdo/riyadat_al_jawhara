@props(['url'])
@php
    $logo = $settings?->logoLightUrl();
    $name = __('site.meta.site_name');
@endphp
<tr>
<td class="header" bgcolor="#2B2B2B" align="center">
<a href="{{ $url }}" style="display: inline-block;">
@if ($logo)
{{-- Absolute URL; the alt text (company name) shows when images are blocked. --}}
<img src="{{ $logo }}" class="logo" alt="{{ $name }}" height="64">
@endif
<span class="brand-name">{{ $name }}</span>
</a>
</td>
</tr>
<tr>
<td class="header-accent" bgcolor="#C9A45A" height="3">&nbsp;</td>
</tr>
