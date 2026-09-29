@props([
    'title' => null,
    'description' => null,
    // Absolute URL of the page's own share image (entity cover); falls back to the default.
    'image' => null,
    // "website" or "article" (blog posts).
    'type' => 'website',
    'publishedAt' => null,
    'modifiedAt' => null,
    // [['name' => ..., 'url' => ...], ...] after "Home"; emitted as BreadcrumbList.
    'breadcrumbs' => [],
    // e.g. "noindex, follow" for search-result pages.
    'robots' => null,
])
@php
    $jsonLdSettings = \App\Models\Setting::current();
    $siteName = __('site.meta.site_name');

    $pageTitle = $title ? $title.' | '.$siteName : $siteName.' | '.__('site.meta.tagline');
    $metaTitle = $title ?? $siteName;
    // Plain text, single line, ~160 chars: what search engines actually show.
    $metaDescription = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($description ?: __('site.meta.default_description'))))), 160);
    $shareImage = $image ?: $jsonLdSettings->ogImageUrl();

    // Canonical = this page in the current language, without filter/search
    // query strings (views of the same page); only ?page=N (N > 1) is kept.
    // Each language version is its own canonical and they hreflang each other.
    $canonical = \App\Support\LocalizedUrl::canonical(app()->getLocale());
    $alternates = collect(\App\Support\LocalizedUrl::supported())
        ->mapWithKeys(fn ($locale) => [$locale => \App\Support\LocalizedUrl::canonical($locale)]);

    $localBusiness = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $siteName,
        'url' => url('/'),
        'telephone' => $jsonLdSettings->phone,
        'email' => $jsonLdSettings->email,
        'logo' => $jsonLdSettings->logoUrl(),
        'image' => $jsonLdSettings->ogImageUrl(),
        'address' => $jsonLdSettings->address ? [
            '@type' => 'PostalAddress',
            'streetAddress' => $jsonLdSettings->address,
            'addressCountry' => 'SA',
        ] : null,
        'geo' => ($jsonLdSettings->map_lat && $jsonLdSettings->map_lng) ? [
            '@type' => 'GeoCoordinates',
            'latitude' => (float) $jsonLdSettings->map_lat,
            'longitude' => (float) $jsonLdSettings->map_lng,
        ] : null,
        'sameAs' => array_values($jsonLdSettings->socialLinks()),
    ]);

    $breadcrumbList = null;
    if ($breadcrumbs !== []) {
        $items = array_merge([['name' => __('site.nav.home'), 'url' => route('home')]], $breadcrumbs);
        $breadcrumbList = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn ($item, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ], $items, array_keys($items)),
        ];
    }

    $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    @if ($robots)
        <meta name="robots" content="{{ $robots }}">
    @endif
    <link rel="canonical" href="{{ $canonical }}">

    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:locale" content="{{ app()->getLocale() === 'ar' ? 'ar_SA' : 'en_US' }}">
    <meta property="og:locale:alternate" content="{{ app()->getLocale() === 'ar' ? 'en_US' : 'ar_SA' }}">
    <meta property="og:image" content="{{ $shareImage }}">
    @unless ($image)
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endunless
    <meta property="og:image:alt" content="{{ $metaTitle }}">
    @if ($type === 'article')
        @if ($publishedAt)
            <meta property="article:published_time" content="{{ $publishedAt->toIso8601String() }}">
        @endif
        @if ($modifiedAt)
            <meta property="article:modified_time" content="{{ $modifiedAt->toIso8601String() }}">
        @endif
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $shareImage }}">

    @foreach ($alternates as $locale => $alternateUrl)
        <link rel="alternate" hreflang="{{ $locale }}" href="{{ $alternateUrl }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $alternates[config('locales.default')] }}">

    <script type="application/ld+json">{!! json_encode($localBusiness, $jsonFlags) !!}</script>
    @if ($breadcrumbList)
        <script type="application/ld+json">{!! json_encode($breadcrumbList, $jsonFlags) !!}</script>
    @endif

    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon-48x48.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#2B2B2B">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"></noscript>

    @stack('head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream text-charcoal {{ app()->getLocale() === 'ar' ? 'font-arabic' : 'font-sans' }} antialiased scroll-smooth">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:start-4 focus:z-[60] focus:rounded-md focus:bg-charcoal focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-cream">
        {{ __('site.common.skip_to_content') }}
    </a>

    <div class="min-h-screen flex flex-col">
        @include('partials.header')

        <main id="main-content" class="flex-1">
            {{ $slot }}
        </main>

        @include('partials.footer')
    </div>

    <button
        x-data="{ visible: false }"
        x-show="visible"
        x-cloak
        x-transition.opacity.duration.200ms
        @scroll.window="visible = window.scrollY > 480"
        @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        aria-label="{{ __('site.common.back_to_top') }}"
        style="bottom: calc(5.5rem + env(safe-area-inset-bottom));"
        class="fixed end-[1.375rem] z-40 inline-flex h-11 w-11 items-center justify-center rounded-full bg-charcoal text-cream shadow-lg transition-colors hover:bg-charcoal-700 focus-visible:ring-2 focus-visible:ring-charcoal focus-visible:ring-offset-2"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
    </button>

    <x-whatsapp-float />
</body>
</html>
