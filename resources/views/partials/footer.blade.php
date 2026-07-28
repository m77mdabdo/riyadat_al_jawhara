@php
    $settings = $settings ?? \App\Models\Setting::current();
@endphp

<footer class="bg-charcoal text-cream mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
        <div>
            <div class="flex items-center gap-3 mb-4">
                <span class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg bg-stone-700">
                    <svg class="absolute -top-1.5 start-1/2 -translate-x-1/2 h-3 w-3 text-cream" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3 6-3 14-3-14 3-6z"/></svg>
                    <span class="font-arabic text-cream font-extrabold text-base">JRA</span>
                </span>
                <span class="font-bold">{{ __('site.meta.site_name') }}</span>
            </div>
            <p class="text-sm text-stone-300 leading-relaxed">{{ __('site.meta.tagline') }}</p>
        </div>

        <div>
            <h3 class="font-semibold mb-4">{{ __('site.footer.quick_links') }}</h3>
            <ul class="space-y-2 text-sm text-stone-300">
                <li><a href="{{ route('services.index') }}" class="hover:text-cream">{{ __('site.nav.services') }}</a></li>
                <li><a href="{{ route('stones.index') }}" class="hover:text-cream">{{ __('site.nav.stones') }}</a></li>
                <li><a href="{{ route('projects.index') }}" class="hover:text-cream">{{ __('site.nav.projects') }}</a></li>
                <li><a href="{{ route('about') }}" class="hover:text-cream">{{ __('site.nav.about') }}</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-cream">{{ __('site.nav.contact') }}</a></li>
            </ul>
        </div>

        <div>
            <h3 class="font-semibold mb-4">{{ __('site.footer.contact_info') }}</h3>
            <ul class="space-y-2 text-sm text-stone-300">
                @if ($settings->phone)
                    <li>{{ $settings->phone }}</li>
                @endif
                @if ($settings->email)
                    <li>{{ $settings->email }}</li>
                @endif
                @if ($settings->address)
                    <li>{{ $settings->address }}</li>
                @endif
                @if ($settings->working_hours)
                    <li class="pt-1 text-stone-400">{{ $settings->working_hours }}</li>
                @endif
            </ul>
        </div>

        <div>
            <h3 class="font-semibold mb-4">{{ __('site.footer.follow_us') }}</h3>
            <div class="flex items-center gap-3">
                @if ($settings->facebook_url)
                    <a href="{{ $settings->facebook_url }}" target="_blank" rel="noopener" class="h-9 w-9 flex items-center justify-center rounded-full bg-stone-700 hover:bg-stone-600" aria-label="Facebook">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12a10 10 0 10-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.4h-1.2c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0022 12z"/></svg>
                    </a>
                @endif
                @if ($settings->instagram_url)
                    <a href="{{ $settings->instagram_url }}" target="_blank" rel="noopener" class="h-9 w-9 flex items-center justify-center rounded-full bg-stone-700 hover:bg-stone-600" aria-label="Instagram">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2c2.7 0 3 0 4.1.06 1.1.05 1.8.22 2.5.47.7.27 1.2.6 1.8 1.15.5.5.9 1.1 1.15 1.8.25.7.42 1.4.47 2.5.06 1.1.06 1.4.06 4.1s0 3-.06 4.1c-.05 1.1-.22 1.8-.47 2.5-.27.7-.6 1.2-1.15 1.8-.5.5-1.1.9-1.8 1.15-.7.25-1.4.42-2.5.47-1.1.06-1.4.06-4.1.06s-3 0-4.1-.06c-1.1-.05-1.8-.22-2.5-.47-.7-.27-1.2-.6-1.8-1.15-.5-.5-.9-1.1-1.15-1.8-.25-.7-.42-1.4-.47-2.5C2 15 2 14.7 2 12s0-3 .06-4.1c.05-1.1.22-1.8.47-2.5.27-.7.6-1.2 1.15-1.8.5-.5 1.1-.9 1.8-1.15.7-.25 1.4-.42 2.5-.47C9 2 9.3 2 12 2zm0 1.8c-2.6 0-2.9 0-4 .06-.9.04-1.4.18-1.7.3-.44.17-.75.37-1.08.7-.33.33-.53.64-.7 1.08-.12.3-.26.8-.3 1.7-.06 1.1-.06 1.4-.06 4s0 2.9.06 4c.04.9.18 1.4.3 1.7.17.44.37.75.7 1.08.33.33.64.53 1.08.7.3.12.8.26 1.7.3 1.1.06 1.4.06 4 .06s2.9 0 4-.06c.9-.04 1.4-.18 1.7-.3.44-.17.75-.37 1.08-.7.33-.33.53-.64.7-1.08.12-.3.26-.8.3-1.7.06-1.1.06-1.4.06-4s0-2.9-.06-4c-.04-.9-.18-1.4-.3-1.7-.17-.44-.37-.75-.7-1.08a2.9 2.9 0 00-1.08-.7c-.3-.12-.8-.26-1.7-.3-1.1-.06-1.4-.06-4-.06zm0 3.5a4.7 4.7 0 110 9.4 4.7 4.7 0 010-9.4zm0 1.8a2.9 2.9 0 100 5.8 2.9 2.9 0 000-5.8zm5.9-2a1.1 1.1 0 11-2.2 0 1.1 1.1 0 012.2 0z"/></svg>
                    </a>
                @endif
                @if ($settings->tiktok_url)
                    <a href="{{ $settings->tiktok_url }}" target="_blank" rel="noopener" class="h-9 w-9 flex items-center justify-center rounded-full bg-stone-700 hover:bg-stone-600" aria-label="TikTok">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M16.6 5.8a4.5 4.5 0 01-3.3-1.5v9.7a5.4 5.4 0 11-4.6-5.4v2.2a3.2 3.2 0 102.6 3.2V2h2.2a4.5 4.5 0 002.2 3.9 4.5 4.5 0 00.9.4v2.2a6.7 6.7 0 01-2-.7z"/></svg>
                    </a>
                @endif
                @if ($settings->snapchat_url)
                    <a href="{{ $settings->snapchat_url }}" target="_blank" rel="noopener" class="h-9 w-9 flex items-center justify-center rounded-full bg-stone-700 hover:bg-stone-600" aria-label="Snapchat">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2c3 0 5 2.3 5 5.4 0 1 0 2 .1 2.6.1.2.4.3.8.3.5-.1 1-.4 1.3-.4.4 0 1 .2 1 .8 0 .5-.5.8-1 1-.3.1-.6.3-.6.6 0 .8 1.6 2.7 3.4 3-.1.6-1 1-1.7 1.2-.3 0-.5.2-.6.5-.1.3-.2.7-.4.9-.3.3-1 .2-1.7.1-.6-.1-1.3-.2-1.9 0-.6.2-1.1.7-1.7 1.1-.6.5-1.3 1-2.4 1s-1.8-.5-2.4-1c-.6-.4-1.1-.9-1.7-1.1-.6-.2-1.3-.1-1.9 0-.7.1-1.4.2-1.7-.1-.2-.2-.3-.6-.4-.9-.1-.3-.3-.5-.6-.5-.7-.2-1.6-.6-1.7-1.2 1.8-.3 3.4-2.2 3.4-3 0-.3-.3-.5-.6-.6-.5-.2-1-.5-1-1 0-.6.6-.8 1-.8.3 0 .8.3 1.3.4.4 0 .7-.1.8-.3.1-.6.1-1.6.1-2.6C7 4.3 9 2 12 2z"/></svg>
                    </a>
                @endif
                @if ($settings->whatsapp)
                    <a href="https://wa.me/{{ $settings->whatsapp }}" target="_blank" rel="noopener" class="h-9 w-9 flex items-center justify-center rounded-full bg-stone-700 hover:bg-stone-600" aria-label="WhatsApp">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 00-8.6 15L2 22l5.2-1.4A10 10 0 1012 2zm0 1.8a8.2 8.2 0 016.9 12.6l-.3.5.6 2.2-2.3-.6-.5.3a8.2 8.2 0 11-4.4-15zm4.5 10.2c-.2-.1-1.3-.6-1.5-.7-.2-.1-.4-.1-.5.1-.2.2-.6.7-.7.9-.1.1-.3.1-.5 0-.2-.1-1-.4-1.9-1.2-.7-.6-1.2-1.4-1.3-1.6-.1-.2 0-.3.1-.5.1-.1.2-.3.4-.4.1-.1.2-.3.2-.4.1-.2 0-.3 0-.5-.1-.1-.5-1.3-.7-1.7-.2-.5-.4-.4-.5-.4h-.5c-.2 0-.4.1-.6.3-.2.2-.8.8-.8 1.9s.8 2.2 1 2.4c.1.1 1.6 2.5 3.9 3.4.5.2 1 .4 1.3.5.5.2 1 .1 1.4.1.4-.1 1.3-.5 1.5-1 .2-.5.2-.9.1-1z"/></svg>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="border-t border-stone-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-xs text-stone-400 text-center">
            &copy; {{ now()->year }} {{ __('site.meta.site_name') }} — {{ __('site.footer.rights_reserved') }}
        </div>
    </div>
</footer>
