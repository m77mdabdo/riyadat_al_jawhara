{{-- Specification rows for one stone: origin, local/imported and the active attribute groups. --}}
<table class="specs">
    @if (empty($skipOrigin) && $stone->origin)
        <tr><td class="label">{{ __('site.stones.origin') }}</td><td>{{ $stone->origin }}</td></tr>
    @endif
    @if ($stone->origin_type)
        <tr><td class="label">{{ __('site.stones.origin_type') }}</td><td>{{ __('site.common.'.$stone->origin_type) }}</td></tr>
    @endif
    @foreach ($groups as $type => $values)
        <tr><td class="label">{{ __('site.stones.'.$type) }}</td><td>{{ $values->pluck('name')->implode(app()->getLocale() === 'ar' ? '، ' : ', ') }}</td></tr>
    @endforeach
</table>
