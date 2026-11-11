@php
    use App\Support\SiteImages;
    use Illuminate\Support\Str;

    $rtl = $locale === 'ar';
    $start = $rtl ? 'right' : 'left';
    $end = $rtl ? 'left' : 'right';
    // Display face: Cairo Black for Arabic headings, Marcellus for English (as on the website).
    $display = $rtl ? 'cairoblack' : 'marcellus';
    $logoDark = $settings->getFirstMedia('logo')?->getPath() ?? public_path(\App\Models\Setting::DEFAULT_LOGO);
    $logoLight = $settings->getFirstMedia('logo_light')?->getPath() ?? public_path('logo-full-white-2000.png');
    $coverPhoto = collect(['cinema-quarry', 'hero', 'about'])->map(fn ($slot) => SiteImages::exists($slot) ? SiteImages::path($slot) : null)->filter()->first();
    $address = $rtl ? $settings->address_ar : $settings->address_en;
    $hours = $rtl ? $settings->working_hours_ar : $settings->working_hours_en;
    $site = route('home', ['locale' => $locale]);

    // Local files only (mPDF never fetches over HTTP). Covers use the original
    // upload for print sharpness; gallery thumbnails the smaller rendition.
    $mediaPath = function ($media, array $conversions = []) {
        if (! $media) {
            return null;
        }
        foreach ($conversions as $conversion) {
            if ($media->hasGeneratedConversion($conversion) && is_file($media->getPath($conversion))) {
                return $media->getPath($conversion);
            }
        }

        return is_file($media->getPath()) ? $media->getPath() : null;
    };
    $count = $categories->sum(fn ($category) => $category->stoneTypes->count());
@endphp
<html dir="{{ $rtl ? 'rtl' : 'ltr' }}" lang="{{ $locale }}">
<head>
<style>
    @page content {
        background-color: #FFFFFF;
        margin: 30mm 16mm 22mm 16mm;
        margin-header: 9mm;
        margin-footer: 9mm;
        header: html_pageHeader;
        footer: html_pageFooter;
    }
    @if ($single)
    @page {
        margin: 30mm 16mm 22mm 16mm;
        margin-header: 9mm;
        margin-footer: 9mm;
        header: html_pageHeader;
        footer: html_pageFooter;
    }
    @else
    {{-- The catalogue opens on its cover: full bleed, no header or footer. --}}
    @page { margin: 0; margin-header: 0; margin-footer: 0; background-color: #1C1C1C; }
    @endif
    @page cover { margin: 0; margin-header: 0; margin-footer: 0; header: _blank; footer: _blank; background-color: #1C1C1C; }
    @page back { margin: 0; margin-header: 0; margin-footer: 0; header: _blank; footer: _blank; background-color: #1C1C1C; }

    body { font-family: cairo; font-size: 9.5pt; color: #2B2B2B; line-height: 1.55; }
    .display { font-family: {{ $display }}; }
    .gold { color: #946F2B; }
    .muted { color: #726D67; }
    .ltr { direction: ltr; }

    /* Cover and back cover */
    .cover, .back { color: #F8F6F3; }
    .cover-photo { width: 210mm; height: 168mm; }
    .cover-body { padding: 14mm 18mm 0 18mm; text-align: {{ $start }}; }
    .rule { width: 26mm; height: 0.8mm; background-color: #C9A45A; }
    .kicker { color: #C9A45A; font-size: 9pt; letter-spacing: {{ $rtl ? '0' : '1.5pt' }}; }
    .cover-title { font-size: 34pt; line-height: 1.15; color: #FFFFFF; margin: 4mm 0 2mm; }
    .cover-tagline { color: #C9A45A; font-size: 12pt; }
    .cover-meta { color: #A6A099; font-size: 9pt; }

    /* Running header */
    .header td { font-size: 8pt; color: #726D67; vertical-align: bottom; padding-bottom: 2mm; }
    .header { border-bottom: 0.3mm solid #C9A45A; }

    /* Category band */
    .band { background-color: #2B2B2B; color: #F8F6F3; padding: 5mm 7mm; margin: 0 0 6mm; }
    .band-title { font-size: 20pt; color: #FFFFFF; }
    .band-count { color: #C9A45A; font-size: 8.5pt; }
    .band-text { color: #D5D1CC; font-size: 9pt; margin-top: 1mm; }

    /* Stone entry */
    table.stone { width: 100%; border-collapse: collapse; margin-bottom: 7mm; page-break-inside: avoid; }
    table.stone td { vertical-align: top; }
    .stone-photo { width: 74mm; }
    .stone-body { padding-{{ $start }}: 6mm; }
    .stone-name { font-size: 16pt; line-height: 1.45; color: #1C1C1C; }
    .stone-meta { color: #946F2B; font-size: 8.5pt; margin: 1mm 0 2.5mm; }
    .stone-text { color: #403D39; margin-bottom: 2.5mm; }
    table.specs { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
    table.specs td { padding: 1.4mm 0; border-bottom: 0.2mm solid #E8DAC0; vertical-align: top; }
    table.specs td.label { color: #946F2B; width: 30mm; font-weight: bold; }
    .link { font-size: 8pt; color: #946F2B; margin-top: 2mm; }
    .divider { height: 0.2mm; background-color: #E8DAC0; margin: 0 0 7mm; }

    /* Spec sheet */
    .sheet-photo { width: 178mm; height: 84mm; }
    .sheet-name { font-size: 24pt; line-height: 1.15; color: #1C1C1C; margin-top: 2mm; }
    .section-title { font-size: 11pt; color: #1C1C1C; border-bottom: 0.4mm solid #C9A45A; padding-bottom: 1mm; margin: 5mm 0 2.5mm; }
    .thumb { width: 56mm; height: 34mm; }

    /* Contact */
    table.contact { width: 100%; border-collapse: collapse; }
    table.contact td { padding: 2.4mm 0; border-bottom: 0.2mm solid #403D39; font-size: 10pt; vertical-align: top; color: #F8F6F3; }
    table.contact td.label { color: #C9A45A; width: 38mm; font-size: 9pt; }
    .strip { background-color: #F3EBDD; padding: 3.5mm 6mm; margin-top: 6mm; font-size: 8.5pt; }
</style>
</head>
<body>

<htmlpageheader name="pageHeader">
    <table class="header" width="100%">
        <tr>
            <td style="text-align: {{ $start }};"><img src="{{ $logoDark }}" style="height: 11mm;" alt=""></td>
            <td style="text-align: {{ $end }};">{{ $single ? __('site.catalogue.spec_title') : __('site.catalogue.title') }}</td>
        </tr>
    </table>
</htmlpageheader>
<htmlpagefooter name="pageFooter">
    @include('pdf.footer', ['rtl' => $rtl])
</htmlpagefooter>

@if ($single)
    {{-- One-page specification sheet --}}
    @php
        $stone = $single;
        $cover = $mediaPath($stone->getFirstMedia('cover'));
        $gallery = $stone->getMedia('gallery')->map(fn ($m) => $mediaPath($m, ['medium', 'card', 'thumb']))->filter()->take(3);
        $groups = $stone->specificationGroups();
    @endphp
    @if ($cover)
        <img src="{{ $cover }}" class="sheet-photo" style="width: 178mm; height: 84mm;" alt="">
    @endif
    <div class="kicker" style="color: #946F2B; margin-top: 6mm;">{{ $stone->stoneCategory?->name }}</div>
    <div class="sheet-name display">{{ $stone->name }}</div>
    <div class="rule" style="margin: 3mm 0 0;"></div>

    <div class="section-title display">{{ __('site.stones.specifications') }}</div>
    @include('pdf.partials.specs', ['stone' => $stone, 'groups' => $groups])

    @if ($stone->description)
        <p class="stone-text" style="margin-top: 5mm;">{{ Str::limit($stone->description, 1200) }}</p>
    @endif

    @if ($gallery->isNotEmpty())
        <div class="section-title display">{{ __('site.catalogue.gallery') }}</div>
        <table width="100%" style="border-collapse: collapse;"><tr>
            @foreach ($gallery as $image)
                <td style="width: 60mm; text-align: {{ $start }};"><img src="{{ $image }}" class="thumb" style="width: 56mm; height: 34mm;" alt=""></td>
            @endforeach
            @for ($i = $gallery->count(); $i < 3; $i++)
                <td style="width: 60mm;"></td>
            @endfor
        </tr></table>
    @endif

    <div class="strip">
        <b>{{ __('site.catalogue.contact_title') }}</b> ·
        @if ($settings->phone) <span dir="ltr">{{ $settings->phone }}</span> · @endif
        @if ($settings->email) <span dir="ltr">{{ $settings->email }}</span> · @endif
        <span dir="ltr">{{ route('stones.show', ['locale' => $locale, 'stoneType' => $stone]) }}</span><br>
        <span class="muted">{{ __('site.catalogue.disclaimer') }}</span>
    </div>
@else
    {{-- Cover --}}
    <div class="cover">
        @if ($coverPhoto)
            <img src="{{ $coverPhoto }}" class="cover-photo" style="width: 210mm; height: 168mm;" alt="">
        @endif
        <div class="cover-body">
            <img src="{{ $logoLight }}" style="height: 26mm;" alt="">
            <div class="rule" style="margin: 8mm 0 5mm;"></div>
            <div class="kicker">{{ __('site.catalogue.kicker') }}</div>
            <div class="cover-title display">{{ __('site.catalogue.title') }}</div>
            <div class="cover-tagline">{{ __('site.meta.tagline') }}</div>
            <div class="cover-meta" style="margin-top: 6mm;">{{ __('site.catalogue.generated', ['date' => now()->format('Y-m-d')]) }} · {{ trans_choice('site.catalogue.stones_count', $count) }}</div>
        </div>
    </div>

    <pagebreak page-selector="content" />

    @foreach ($categories as $category)
        <div class="band">
            <div class="band-count">{{ trans_choice('site.catalogue.stones_count', $category->stoneTypes->count()) }}</div>
            <div class="band-title display">{{ $category->name }}</div>
            @if ($category->description)
                <div class="band-text">{{ Str::limit($category->description, 260) }}</div>
            @endif
        </div>

        @foreach ($category->stoneTypes as $stone)
            @php $img = $mediaPath($stone->getFirstMedia('cover')); @endphp
            <table class="stone">
                <tr>
                    @if ($img)
                        <td class="stone-photo"><img src="{{ $img }}" style="width: 72mm; height: 54mm;" alt=""></td>
                    @endif
                    <td class="stone-body">
                        <div class="stone-name display">{{ $stone->name }}</div>
                        <div class="stone-meta">{{ $category->name }}@if ($stone->origin) · {{ $stone->origin }}@endif</div>
                        @if ($stone->description)
                            <div class="stone-text">{{ Str::limit($stone->description, 260) }}</div>
                        @endif
                        @include('pdf.partials.specs', ['stone' => $stone, 'groups' => $stone->specificationGroups(), 'skipOrigin' => true])
                        <div class="link">{{ __('site.catalogue.view_online') }}: <span dir="ltr">{{ route('stones.show', ['locale' => $locale, 'stoneType' => $stone]) }}</span></div>
                    </td>
                </tr>
            </table>
        @endforeach
    @endforeach

    {{-- Back cover: how to reach the company --}}
    <pagebreak page-selector="back" />
    <div class="back">
        <div style="padding: 40mm 22mm 0 22mm; text-align: {{ $start }};">
            <img src="{{ $logoLight }}" style="height: 30mm;" alt="">
            <div class="rule" style="margin: 10mm 0 6mm;"></div>
            <div class="cover-title display" style="font-size: 26pt;">{{ __('site.catalogue.contact_title') }}</div>
            <div style="color: #D5D1CC; font-size: 11pt; margin: 2mm 0 10mm;">{{ __('site.catalogue.contact_intro') }}</div>
            <table class="contact">
                @if ($settings->phone)
                    <tr><td class="label">{{ __('site.catalogue.phone') }}</td><td><span dir="ltr">{{ $settings->phone }}</span></td></tr>
                @endif
                @if ($settings->whatsapp)
                    <tr><td class="label">{{ __('site.catalogue.whatsapp') }}</td><td><span dir="ltr">+{{ ltrim($settings->whatsapp, '+') }}</span></td></tr>
                @endif
                @if ($settings->email)
                    <tr><td class="label">{{ __('site.catalogue.email') }}</td><td><span dir="ltr">{{ $settings->email }}</span></td></tr>
                @endif
                @if ($address)
                    <tr><td class="label">{{ __('site.catalogue.address') }}</td><td>{{ $address }}</td></tr>
                @endif
                @if ($hours)
                    <tr><td class="label">{{ __('site.catalogue.hours') }}</td><td>{{ $hours }}</td></tr>
                @endif
                <tr><td class="label">{{ __('site.catalogue.website') }}</td><td><span dir="ltr">{{ $site }}</span></td></tr>
            </table>
            <div style="color: #A6A099; font-size: 8.5pt; margin-top: 12mm;">{{ __('site.catalogue.disclaimer') }}</div>
        </div>
    </div>
@endif
</body>
</html>
