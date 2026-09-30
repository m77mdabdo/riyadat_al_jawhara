@php
    $settings = \App\Models\Setting::current();
    $rtl = $locale === 'ar';
    $logo = $settings->getFirstMedia('logo')?->getPath() ?? public_path(\App\Models\Setting::DEFAULT_LOGO);
    // Local file for mPDF (no HTTP fetches): WebP card, else JPEG thumb, else original.
    $imagePath = function ($stone) {
        $media = $stone->getFirstMedia('cover');
        if (! $media) {
            return null;
        }
        foreach (['card', 'thumb'] as $conversion) {
            if ($media->hasGeneratedConversion($conversion) && is_file($media->getPath($conversion))) {
                return $media->getPath($conversion);
            }
        }

        return is_file($media->getPath()) ? $media->getPath() : null;
    };
@endphp
<html dir="{{ $rtl ? 'rtl' : 'ltr' }}" lang="{{ $locale }}">
<head>
<style>
    body { font-family: dejavusans; font-size: 10pt; color: #2B2B2B; }
    h1 { font-size: 22pt; margin: 0; }
    h2 { font-size: 15pt; color: #2B2B2B; border-bottom: 2px solid #946F2B; padding-bottom: 4px; margin: 18px 0 10px; }
    h3 { font-size: 12pt; margin: 0 0 3px; }
    .muted { color: #59554F; }
    .gold { color: #946F2B; }
    .cover { text-align: center; padding-top: 60px; }
    .cover p { font-size: 11pt; }
    table.stone { width: 100%; border: 1px solid #E8DAC0; margin-bottom: 10px; }
    table.stone td { vertical-align: top; padding: 6px; }
    .img { width: 38mm; }
    .spec { font-size: 9pt; }
    .spec b { color: #59554F; }
    .contact { margin-top: 30px; font-size: 9.5pt; text-align: center; }
</style>
</head>
<body>
@unless ($single)
    <div class="cover">
        <img src="{{ $logo }}" style="height: 38mm;" alt="">
        <h1>{{ __('site.catalogue.title') }}</h1>
        <p class="gold">{{ __('site.meta.tagline') }}</p>
        <p class="muted">{{ __('site.catalogue.generated', ['date' => now()->format('Y-m-d')]) }}</p>
    </div>
    <pagebreak />
@endunless

@foreach ($categories as $category)
    <h2>{{ $category->name }}</h2>
    @if ($category->description)
        <p class="muted">{{ $category->description }}</p>
    @endif

    @foreach ($category->stoneTypes as $stone)
        @php $img = $imagePath($stone); $groups = $stone->specificationGroups(); @endphp
        <table class="stone" autosize="1">
            <tr>
                @if ($img)
                    <td class="img"><img src="{{ $img }}" style="width: 36mm;" alt=""></td>
                @endif
                <td>
                    <h3>{{ $stone->name }}</h3>
                    @if ($stone->origin)
                        <div class="spec"><b>{{ __('site.stones.origin') }}:</b> {{ $stone->origin }}</div>
                    @endif
                    @if ($stone->origin_type)
                        <div class="spec"><b>{{ __('site.stones.origin_type') }}:</b> {{ __('site.common.'.$stone->origin_type) }}</div>
                    @endif
                    @foreach ($groups as $type => $values)
                        <div class="spec"><b>{{ __('site.stones.'.$type) }}:</b> {{ $values->pluck('name')->implode('، ') }}</div>
                    @endforeach
                    @if ($stone->description)
                        <p>{{ \Illuminate\Support\Str::limit($stone->description, $single ? 1200 : 320) }}</p>
                    @endif
                    <div class="spec gold">{{ route('stones.show', ['locale' => $locale, 'stoneType' => $stone]) }}</div>
                </td>
            </tr>
        </table>
    @endforeach
@endforeach

<div class="contact">
    <b>{{ __('site.meta.site_name') }}</b><br>
    @if ($settings->phone) {{ $settings->phone }} @endif
    @if ($settings->email) · {{ $settings->email }} @endif
    · {{ route('home', ['locale' => $locale]) }}<br>
    <span class="muted">{{ __('site.catalogue.disclaimer') }}</span>
</div>
</body>
</html>
