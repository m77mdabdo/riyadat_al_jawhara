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
    <title>{{ isset($title) ? $title.' | '.__('site.meta.site_name') : __('site.meta.site_name').' | '.__('site.meta.tagline') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-cream text-charcoal {{ app()->getLocale() === 'ar' ? 'font-arabic' : 'font-sans' }} antialiased">
    <div class="min-h-screen flex flex-col">
        @include('partials.header')

        <main class="flex-1">
            {{ $slot }}
        </main>

        @include('partials.footer')
    </div>

    @livewireScripts
</body>
</html>
