@props(['rows'])
@foreach ($rows as $label => $value)
@if (filled($value))
{{ $label }}: {{ trim(strip_tags($value instanceof \Illuminate\Contracts\Support\Htmlable ? $value->toHtml() : (string) $value)) }}
@endif
@endforeach
