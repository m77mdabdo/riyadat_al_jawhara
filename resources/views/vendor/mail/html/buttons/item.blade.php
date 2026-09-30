@props(['url', 'color' => 'primary'])
@php
    $primary = $color !== 'secondary';
@endphp
<td class="button-cell" style="padding: 4px;">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center" bgcolor="{{ $primary ? '#C9A45A' : '#FFFFFF' }}" style="border-radius: 6px; {{ $primary ? '' : 'border: 1px solid #2B2B2B;' }}">
<a href="{{ $url }}" target="_blank" rel="noopener" style="display: inline-block; padding: 11px 20px; font-size: 15px; font-weight: bold; line-height: 20px; color: #2B2B2B; text-decoration: none; border-radius: 6px; white-space: nowrap;">{!! trim($slot) !!}</a>
</td>
</tr>
</table>
</td>
