@props(['rows'])
{{-- Two-column label/value table. $rows: label => value (null/empty values are skipped). --}}
<table class="details" width="100%" cellpadding="0" cellspacing="0" role="presentation">
@foreach ($rows as $label => $value)
@if (filled($value))
<tr>
<th align="{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ $label }}</th>
<td dir="auto">{!! $value instanceof \Illuminate\Contracts\Support\Htmlable ? $value->toHtml() : e($value) !!}</td>
</tr>
@endif
@endforeach
</table>
