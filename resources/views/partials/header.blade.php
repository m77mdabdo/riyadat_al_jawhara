@php
    $navLinks = [
        ['route' => 'home', 'label' => __('site.nav.home')],
        ['route' => 'services.index', 'label' => __('site.nav.services')],
        ['route' => 'stones.index', 'label' => __('site.nav.stones')],
        ['route' => 'projects.index', 'label' => __('site.nav.projects')],
        ['route' => 'gallery', 'label' => __('site.nav.gallery')],
        ['route' => 'blog.index', 'label' => __('site.nav.blog')],
        ['route' => 'faq', 'label' => __('site.nav.faq')],
        ['route' => 'about', 'label' => __('site.nav.about')],
        ['route' => 'contact', 'label' => __('site.nav.contact')],
    ];

    // "services.index" is also active on "services.show", etc.
    $isActive = function (string $route): bool {
        $base = str_ends_with($route, '.index') ? substr($route, 0, -6) : $route;

        return request()->routeIs($route, $base.'.*');
    };
@endphp

<header
    class="bg-cream/95 backdrop-blur border-b border-stone-200 sticky top-0 z-40"
    x-data="{
        open: false,
        toggle() {
            this.open = ! this.open;
            if (this.open) this.$nextTick(() => this.$refs.menu.querySelector('a')?.focus());
        },
        close(returnFocus = true) {
            if (! this.open) return;
            this.open = false;
            if (returnFocus) this.$refs.toggle.focus();
        },
    }"
    @keydown.escape.window="close()"
    @resize.window.debounce.150ms="if (window.matchMedia('(min-width: 1280px)').matches) close(false)"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 h-20">
            <a href="{{ route('home') }}" class="flex min-h-11 items-center shrink-0" aria-label="{{ __('site.meta.site_name') }} — {{ __('site.nav.home') }}">
                <img src="{{ ($settings ?? \App\Models\Setting::current())->logoUrl() }}" alt="{{ __('site.meta.site_name') }}" width="147" height="128" decoding="async" class="hidden sm:block h-14 w-auto">
                <img src="{{ asset('logo-monogram-header-black@2x.png') }}" alt="{{ __('site.meta.site_name') }}" width="212" height="128" decoding="async" class="block sm:hidden h-10 w-auto">
            </a>

            {{-- Eight links + language switch + CTA only fit comfortably from xl (1280px),
                 especially with the longer Arabic labels; below that the menu button is used. --}}
            <nav class="hidden xl:flex items-center gap-6" aria-label="{{ __('site.common.main_navigation') }}">
                @foreach ($navLinks as $link)
                    <a href="{{ route($link['route']) }}"
                       @if ($isActive($link['route'])) aria-current="page" @endif
                       class="whitespace-nowrap text-sm font-medium transition-colors {{ $isActive($link['route']) ? 'text-charcoal border-b-2 border-charcoal pb-1' : 'text-stone-600 hover:text-charcoal' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="hidden xl:flex items-center gap-4 shrink-0">
                <div class="flex items-center gap-1 text-sm font-semibold border border-stone-300 rounded-full overflow-hidden">
                    <a href="{{ \App\Support\LocalizedUrl::current('ar') }}" hreflang="ar" lang="ar" aria-label="{{ __('site.common.switch_to_arabic') }}" @if (app()->getLocale() === 'ar') aria-current="true" @endif class="px-3 py-1.5 {{ app()->getLocale() === 'ar' ? 'bg-charcoal text-cream' : 'text-stone-700 hover:bg-stone-100' }}">AR</a>
                    <a href="{{ \App\Support\LocalizedUrl::current('en') }}" hreflang="en" lang="en" aria-label="{{ __('site.common.switch_to_english') }}" @if (app()->getLocale() === 'en') aria-current="true" @endif class="px-3 py-1.5 {{ app()->getLocale() === 'en' ? 'bg-charcoal text-cream' : 'text-stone-700 hover:bg-stone-100' }}">EN</a>
                </div>
                <x-button :href="route('contact')" size="sm" class="whitespace-nowrap">
                    {{ __('site.common.request_quote') }}
                </x-button>
            </div>

            <button
                type="button"
                x-ref="toggle"
                @click="toggle()"
                :aria-expanded="open.toString()"
                aria-controls="mobile-menu"
                class="xl:hidden inline-flex h-11 w-11 items-center justify-center rounded-md text-charcoal transition-colors hover:bg-stone-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-charcoal focus-visible:ring-offset-2"
                aria-label="{{ __('site.common.menu') }}"
            >
                <svg x-show="!open" x-transition.opacity.duration.150ms class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="open" x-cloak x-transition.opacity.duration.150ms class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <nav
            id="mobile-menu"
            x-ref="menu"
            x-show="open"
            x-cloak
            x-transition
            @click="if ($event.target.closest('a')) close(false)"
            class="xl:hidden pb-6 space-y-1 max-h-[calc(100svh-5rem)] overflow-y-auto"
            aria-label="{{ __('site.common.main_navigation') }}"
        >
            @foreach ($navLinks as $link)
                <a href="{{ route($link['route']) }}"
                   @if ($isActive($link['route'])) aria-current="page" @endif
                   class="flex min-h-11 items-center rounded-md px-3 text-base font-medium {{ $isActive($link['route']) ? 'bg-stone-100 text-charcoal' : 'text-stone-700 hover:bg-stone-100' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
            <div class="flex items-center gap-2 pt-3 px-3">
                <a href="{{ \App\Support\LocalizedUrl::current('ar') }}" hreflang="ar" lang="ar" aria-label="{{ __('site.common.switch_to_arabic') }}" @if (app()->getLocale() === 'ar') aria-current="true" @endif class="inline-flex h-11 min-w-11 items-center justify-center px-4 text-sm font-semibold rounded-full border border-stone-300 {{ app()->getLocale() === 'ar' ? 'bg-charcoal text-cream' : 'text-stone-600' }}">AR</a>
                <a href="{{ \App\Support\LocalizedUrl::current('en') }}" hreflang="en" lang="en" aria-label="{{ __('site.common.switch_to_english') }}" @if (app()->getLocale() === 'en') aria-current="true" @endif class="inline-flex h-11 min-w-11 items-center justify-center px-4 text-sm font-semibold rounded-full border border-stone-300 {{ app()->getLocale() === 'en' ? 'bg-charcoal text-cream' : 'text-stone-600' }}">EN</a>
            </div>
            <x-button :href="route('contact')" size="sm" class="mx-3 mt-3 w-[calc(100%-1.5rem)] min-h-11">
                {{ __('site.common.request_quote') }}
            </x-button>
        </nav>
    </div>
</header>
