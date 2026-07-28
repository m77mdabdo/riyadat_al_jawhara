@php
    $navLinks = [
        ['route' => 'home', 'label' => __('site.nav.home')],
        ['route' => 'services.index', 'label' => __('site.nav.services')],
        ['route' => 'stones.index', 'label' => __('site.nav.stones')],
        ['route' => 'projects.index', 'label' => __('site.nav.projects')],
        ['route' => 'about', 'label' => __('site.nav.about')],
        ['route' => 'contact', 'label' => __('site.nav.contact')],
    ];
@endphp

<header class="bg-cream/95 backdrop-blur border-b border-stone-200 sticky top-0 z-40" x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
                <span class="relative inline-flex h-11 w-11 items-center justify-center rounded-lg bg-charcoal">
                    <svg class="absolute -top-1.5 start-1/2 -translate-x-1/2 h-3 w-3 text-cream" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3 6-3 14-3-14 3-6z"/></svg>
                    <span class="font-arabic text-cream font-extrabold text-lg tracking-wide">JRA</span>
                </span>
                <span class="hidden sm:flex flex-col leading-tight">
                    <span class="font-bold text-charcoal text-base">{{ __('site.meta.site_name') }}</span>
                    <span class="text-xs text-stone-600">{{ __('site.meta.tagline') }}</span>
                </span>
            </a>

            <nav class="hidden lg:flex items-center gap-8">
                @foreach ($navLinks as $link)
                    <a href="{{ route($link['route']) }}"
                       class="text-sm font-medium transition-colors {{ request()->routeIs($link['route']) || request()->routeIs($link['route'].'.*') ? 'text-charcoal border-b-2 border-charcoal pb-1' : 'text-stone-600 hover:text-charcoal' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="hidden lg:flex items-center gap-4">
                <div class="flex items-center gap-1 text-sm font-semibold border border-stone-300 rounded-full overflow-hidden">
                    <a href="{{ route('locale.switch', 'ar') }}" class="px-3 py-1.5 {{ app()->getLocale() === 'ar' ? 'bg-charcoal text-cream' : 'text-stone-600 hover:bg-stone-100' }}">AR</a>
                    <a href="{{ route('locale.switch', 'en') }}" class="px-3 py-1.5 {{ app()->getLocale() === 'en' ? 'bg-charcoal text-cream' : 'text-stone-600 hover:bg-stone-100' }}">EN</a>
                </div>
                <a href="{{ route('contact') }}" class="inline-flex items-center rounded-md bg-charcoal px-5 py-2.5 text-sm font-semibold text-cream hover:bg-charcoal-700 transition-colors">
                    {{ __('site.common.request_quote') }}
                </a>
            </div>

            <button @click="open = !open" class="lg:hidden inline-flex items-center justify-center rounded-md p-2 text-charcoal" aria-label="{{ __('site.common.menu') }}">
                <svg x-show="!open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="open" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div x-show="open" x-cloak x-transition class="lg:hidden pb-6 space-y-1">
            @foreach ($navLinks as $link)
                <a href="{{ route($link['route']) }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs($link['route']) ? 'bg-stone-100 text-charcoal' : 'text-stone-700 hover:bg-stone-100' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
            <div class="flex items-center gap-2 pt-3 px-3">
                <a href="{{ route('locale.switch', 'ar') }}" class="px-3 py-1.5 text-sm font-semibold rounded-full border border-stone-300 {{ app()->getLocale() === 'ar' ? 'bg-charcoal text-cream' : 'text-stone-600' }}">AR</a>
                <a href="{{ route('locale.switch', 'en') }}" class="px-3 py-1.5 text-sm font-semibold rounded-full border border-stone-300 {{ app()->getLocale() === 'en' ? 'bg-charcoal text-cream' : 'text-stone-600' }}">EN</a>
            </div>
            <a href="{{ route('contact') }}" class="block mx-3 mt-3 rounded-md bg-charcoal px-5 py-2.5 text-center text-sm font-semibold text-cream">
                {{ __('site.common.request_quote') }}
            </a>
        </div>
    </div>
</header>
