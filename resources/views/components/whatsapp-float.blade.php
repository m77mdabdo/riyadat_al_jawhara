@php
    $settings = $settings ?? \App\Models\Setting::current();
@endphp

@if ($settings->whatsapp_url)
    <div
        x-data="{
            showTooltip: false,
            init() {
                if (! sessionStorage.getItem('jra_whatsapp_tooltip_shown')) {
                    setTimeout(() => {
                        this.showTooltip = true;
                        sessionStorage.setItem('jra_whatsapp_tooltip_shown', '1');
                    }, 3000);
                }
            },
        }"
        class="fixed z-50"
        style="right: calc(1rem + env(safe-area-inset-right)); left: auto; bottom: calc(1rem + env(safe-area-inset-bottom) + var(--consent-h, 0px));"
    >
        <div
            x-show="showTooltip"
            x-cloak
            x-transition.opacity.duration.300ms
            class="absolute bottom-full w-64 max-w-[calc(100vw-2rem)] rounded-xl border border-stone-200 bg-white p-4 pr-11 text-sm text-stone-700 shadow-lg"
            style="right: 0; left: auto; margin-bottom: 0.75rem;"
        >
            <button
                @click="showTooltip = false"
                type="button"
                class="absolute h-11 w-11 flex items-center justify-center text-stone-400 hover:text-charcoal focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-charcoal focus-visible:ring-offset-1 rounded"
                style="top: 0; right: 0; left: auto;"
                aria-label="{{ __('site.common.close') }}"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <p>{{ __('site.whatsapp_default_message') }}</p>
        </div>

        <a
            data-wa-track href="{{ $settings->whatsapp_url }}?text={{ urlencode(__('site.whatsapp_default_message')) }}"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="{{ __('site.whatsapp_aria_label') }}"
            class="relative flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] shadow-lg transition hover:scale-105 hover:shadow-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-charcoal focus-visible:ring-offset-2"
        >
            <span class="pointer-events-none absolute inset-0 animate-ping rounded-full bg-[#25D366] opacity-60 [animation-duration:2.5s] motion-reduce:animate-none" aria-hidden="true"></span>
            <svg viewBox="0 0 24 24" fill="white" class="relative h-7 w-7" aria-hidden="true">
                <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.96L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21h.004c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.05 2h-.01zm0 1.67c2.2 0 4.27.86 5.82 2.42a8.225 8.225 0 012.41 5.82c0 4.54-3.7 8.23-8.24 8.23-1.4 0-2.77-.35-4-1.02l-.29-.17-2.98.78.8-2.9-.19-.3a8.196 8.196 0 01-1.25-4.37c0-4.54 3.7-8.23 8.24-8.23l-.32-.06zm-4.52 4.71c-.15 0-.4.06-.61.29-.21.24-.8.78-.8 1.91 0 1.12.82 2.2.93 2.36.11.15 1.6 2.55 3.95 3.48 1.95.77 2.35.62 2.78.58.42-.04 1.36-.56 1.55-1.09.19-.53.19-.99.13-1.09-.06-.09-.21-.15-.44-.26-.23-.12-1.36-.67-1.57-.75-.21-.08-.36-.12-.52.12-.15.23-.6.75-.73.9-.13.15-.27.17-.5.06-.23-.12-.98-.36-1.86-1.15-.69-.61-1.15-1.37-1.29-1.6-.13-.23-.01-.36.1-.47.11-.1.23-.27.35-.4.11-.13.15-.23.23-.38.08-.15.04-.29-.02-.4-.06-.12-.52-1.28-.72-1.75-.19-.46-.38-.4-.52-.4h-.44z" />
            </svg>
        </a>
    </div>
@endif
