{{--
    Auto-submitting filter <select> for listing pages.
    $options: value => label. The first option ("Label: All") clears the filter.
--}}
@props(['name', 'label', 'options', 'selected' => ''])

<select name="{{ $name }}" onchange="this.form.submit()" aria-label="{{ $label }}" {{ $attributes->merge(['class' => 'w-full sm:w-auto min-h-11 rounded-md border-stone-300 px-3 py-2.5 text-base sm:text-sm text-charcoal focus:border-charcoal focus:ring-charcoal']) }}>
    <option value="">{{ $label }}: {{ __('site.common.all') }}</option>
    @foreach ($options as $value => $optionLabel)
        <option value="{{ $value }}" @selected((string) $selected === (string) $value)>{{ $optionLabel }}</option>
    @endforeach
</select>
