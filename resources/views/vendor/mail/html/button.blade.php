@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
{{-- Bulletproof button: table cell with bgcolor (Outlook ignores CSS backgrounds on links). --}}
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<x-mail::buttons>
<x-mail::buttons.item :url="$url" :color="$color">{!! $slot !!}</x-mail::buttons.item>
</x-mail::buttons>
</td>
</tr>
</table>
