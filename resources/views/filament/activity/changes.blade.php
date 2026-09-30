@php
    $properties = $getRecord()->properties;
    $new = (array) ($properties['attributes'] ?? []);
    $old = (array) ($properties['old'] ?? []);
    $fields = array_unique(array_merge(array_keys($old), array_keys($new)));
    $show = fn ($value) => is_scalar($value) || $value === null
        ? \Illuminate\Support\Str::limit(strip_tags((string) ($value ?? '—')), 300)
        : json_encode($value, JSON_UNESCAPED_UNICODE);
@endphp

@if (empty($fields))
    <p class="text-sm text-gray-500">{{ __('admin.activity.no_changes') }}</p>
@else
    <div class="overflow-x-auto">
        <table class="w-full text-start text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-white/10">
                    <th class="py-2 pe-4 text-start font-semibold">{{ __('admin.activity.field') }}</th>
                    <th class="py-2 pe-4 text-start font-semibold">{{ __('admin.activity.old') }}</th>
                    <th class="py-2 text-start font-semibold">{{ __('admin.activity.new') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($fields as $field)
                    <tr class="border-b border-gray-100 align-top dark:border-white/5">
                        <td class="py-2 pe-4 font-mono text-xs">{{ $field }}</td>
                        <td class="py-2 pe-4 text-gray-600 dark:text-gray-400">{{ array_key_exists($field, $old) ? $show($old[$field]) : '—' }}</td>
                        <td class="py-2">{{ array_key_exists($field, $new) ? $show($new[$field]) : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
