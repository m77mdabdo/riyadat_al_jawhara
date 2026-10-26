@props(['code', 'title', 'heading', 'message'])
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} | {{ __('site.meta.site_name') }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-cream text-charcoal {{ app()->getLocale() === 'ar' ? 'font-arabic' : 'font-sans' }} antialiased min-h-screen flex items-center justify-center px-4 py-16">
    <main class="max-w-lg w-full text-center">
        <a href="{{ route('home') }}" class="inline-block mb-10">
            <img src="{{ rescue(fn () => \App\Models\Setting::current()->logoUrl(), asset(\App\Models\Setting::DEFAULT_LOGO), false) }}" alt="{{ __('site.meta.site_name') }}" width="138" height="120" decoding="async" class="h-12 w-auto mx-auto">
        </a>

        <p class="text-sm font-semibold text-stone-600 tracking-[0.2em]">{{ $code }}</p>
        <h1 class="mt-3 text-section-title font-bold text-charcoal">{{ $heading }}</h1>
        <p class="mt-4 text-stone-600 leading-relaxed rtl:leading-loose">{{ $message }}</p>

        <div class="mt-8">
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-md bg-charcoal text-cream px-7 py-3 text-sm font-semibold hover:bg-charcoal-700 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-charcoal focus-visible:ring-offset-2">
                {{ __('site.errors.back_home') }}
            </a>
        </div>

        <div class="mt-8 pt-8 border-t border-stone-200">
            <p class="text-sm text-stone-600 mb-3">{{ __('site.errors.quick_links') }}</p>
            <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm">
                <a href="{{ route('services.index') }}" class="inline-flex min-h-11 items-center text-stone-600 hover:text-charcoal underline underline-offset-2">{{ __('site.nav.services') }}</a>
                <a href="{{ route('projects.index') }}" class="inline-flex min-h-11 items-center text-stone-600 hover:text-charcoal underline underline-offset-2">{{ __('site.nav.projects') }}</a>
                <a href="{{ route('contact') }}" class="inline-flex min-h-11 items-center text-stone-600 hover:text-charcoal underline underline-offset-2">{{ __('site.nav.contact') }}</a>
            </div>
        </div>
    </main>
</body>
</html>
