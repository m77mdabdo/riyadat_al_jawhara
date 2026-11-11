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
    // [type, id] of the service/stone/project a WhatsApp click comes from.
    'waContext' => null,
    // App\Enums\PageKey value: SEO overrides, noindex, share image and
    // content blocks from Website Content → Pages.
    'page' => null,
])
@php
    $pageContent = \App\Support\PageContent::get($page);
    if ($pageContent) {
        $title = $pageContent->seoTitle($title);
        $description = $pageContent->seoDescription($description);
        $image = \App\Support\PageContent::ogImage($pageContent) ?? $image;
        if ($pageContent->noindex) {
            $robots = 'noindex, follow';
        }
    }
    $jsonLdSettings = $settings;
    $siteName = __('site.meta.site_name');

    $pageTitle = $title ? \App\Support\Seo::title($title, $siteName) : $siteName.' | '.__('site.meta.tagline');
    // Settings → SEO: hide the whole site (staging / before launch).
    if (\App\Support\Seo::noindexSite()) {
        $robots = 'noindex, nofollow';
    }
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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    @if ($robots)
        <meta name="robots" content="{{ $robots }}">
    @endif
    <link rel="canonical" href="{{ $canonical }}">
    @foreach (\App\Support\Seo::verifications() as $verificationName => $verificationCode)
        <meta name="{{ $verificationName }}" content="{{ $verificationCode }}">
    @endforeach

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

    @foreach ([16, 32, 48] as $iconSize)
        <link rel="icon" type="image/png" sizes="{{ $iconSize }}x{{ $iconSize }}" href="{{ $jsonLdSettings->faviconUrl($iconSize) ?? asset("favicon-{$iconSize}x{$iconSize}.png") }}">
    @endforeach
    @unless ($jsonLdSettings->faviconUrl(32))
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    @endunless
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $jsonLdSettings->faviconUrl(180) ?? asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#2B2B2B">


    {{-- Above-the-fold fonts only: body + heading face of the page language. --}}
    @foreach (app()->getLocale() === 'ar'
        ? ['node_modules/@fontsource-variable/cairo/files/cairo-arabic-wght-normal.woff2', 'node_modules/@fontsource/reem-kufi/files/reem-kufi-arabic-700-normal.woff2']
        : ['node_modules/@fontsource-variable/inter/files/inter-latin-wght-normal.woff2', 'node_modules/@fontsource/marcellus/files/marcellus-latin-400-normal.woff2'] as $font)
        <link rel="preload" as="font" type="font/woff2" href="{{ \Illuminate\Support\Facades\Vite::asset($font) }}" crossorigin>
    @endforeach
    @stack('head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream text-charcoal {{ app()->getLocale() === 'ar' ? 'font-arabic' : 'font-sans' }} antialiased scroll-smooth"
      data-locale="{{ app()->getLocale() }}"
      data-wa-endpoint="{{ route('whatsapp.click') }}"
      @if ($waContext) data-wa-context-type="{{ $waContext[0] }}" data-wa-context-id="{{ $waContext[1] }}" @endif
      @if ($analyticsConfig = \App\Support\Analytics::clientConfig()) data-analytics="{{ json_encode($analyticsConfig) }}" @endif>
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:start-4 focus:z-[60] focus:rounded-md focus:bg-charcoal focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-cream">
        {{ __('site.common.skip_to_content') }}
    </a>

    <div class="min-h-screen flex flex-col">
        @include('partials.header')

        <main id="main-content" class="flex-1" x-data>
            {{ $slot }}

            @if ($pageContent?->blocks)
                <x-page-blocks :page="$pageContent" />
            @endif
        </main>

        @include('partials.footer')
    </div>

    @if ($jsonLdSettings->option('floating_options', 'back_to_top', true))
    <button
        x-data="{ visible: false }"
        x-show="visible"
        x-cloak
        x-transition.opacity.duration.200ms
        @scroll.window="visible = window.scrollY > 480"
        @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        aria-label="{{ __('site.common.back_to_top') }}"
        style="bottom: calc(5.5rem + env(safe-area-inset-bottom) + var(--consent-h, 0px));"
        class="fixed end-[1.375rem] z-40 inline-flex h-11 w-11 items-center justify-center rounded-full bg-charcoal text-cream shadow-lg transition-colors hover:bg-charcoal-700 focus-visible:ring-2 focus-visible:ring-charcoal focus-visible:ring-offset-2"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
    </button>
    @endif

    <x-whatsapp-float />

    @if (\App\Support\Analytics::enabled())
        <x-consent-banner />
    @endif
</body>
</html>
