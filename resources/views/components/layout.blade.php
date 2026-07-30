@props(['title' => null, 'description' => null])
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $description ?? __('site.meta.default_description') }}">
    <meta property="og:title" content="{{ $title ?? __('site.meta.site_name') }}">
    <meta property="og:description" content="{{ $description ?? __('site.meta.default_description') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="{{ app()->getLocale() === 'ar' ? 'ar_SA' : 'en_US' }}">
    <meta property="og:image" content="{{ asset('og-image-1200x630.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ asset('og-image-1200x630.png') }}">
    <title>{{ isset($title) ? $title.' | '.__('site.meta.site_name') : __('site.meta.site_name').' | '.__('site.meta.tagline') }}</title>

    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon-48x48.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#2B2B2B">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"></noscript>
    @if (request()->routeIs('home'))
        <link rel="preload" as="image" href="{{ asset('images/hero-poster.webp') }}" fetchpriority="high">
    @endif

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
        class="fixed bottom-6 end-6 z-40 inline-flex h-11 w-11 items-center justify-center rounded-full bg-charcoal text-cream shadow-lg transition-colors hover:bg-charcoal-700 focus-visible:ring-2 focus-visible:ring-charcoal focus-visible:ring-offset-2"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
    </button>
</body>
</html>
