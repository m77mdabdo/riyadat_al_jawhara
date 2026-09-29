@php
    $settings = $settings ?? \App\Models\Setting::current();
@endphp

<footer class="bg-charcoal text-cream mt-auto">
    <x-motif.najdi-band tone="light" class="bg-charcoal pt-4" />
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
        <div>
            <img src="{{ $settings->logoLightUrl() }}" alt="{{ __('site.meta.site_name') }}" width="138" height="120" loading="lazy" decoding="async" class="h-12 w-auto mb-3">
            <p class="text-sm text-stone-300 leading-relaxed rtl:leading-loose">{{ __('site.meta.tagline') }}</p>
        </div>

        <div>
            <h3 class="text-base font-semibold mb-4">{{ __('site.footer.quick_links') }}</h3>
            <ul class="text-sm text-stone-300">
                <li><a href="{{ route('services.index') }}" class="inline-flex min-h-11 items-center hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.nav.services') }}</a></li>
                <li><a href="{{ route('stones.index') }}" class="inline-flex min-h-11 items-center hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.nav.stones') }}</a></li>
                <li><a href="{{ route('projects.index') }}" class="inline-flex min-h-11 items-center hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.nav.projects') }}</a></li>
                <li><a href="{{ route('blog.index') }}" class="inline-flex min-h-11 items-center hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.nav.blog') }}</a></li>
                <li><a href="{{ route('faq') }}" class="inline-flex min-h-11 items-center hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.nav.faq') }}</a></li>
                <li><a href="{{ route('about') }}" class="inline-flex min-h-11 items-center hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.nav.about') }}</a></li>
                <li><a href="{{ route('contact') }}" class="inline-flex min-h-11 items-center hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.nav.contact') }}</a></li>
            </ul>
        </div>

        <div>
            <h3 class="text-base font-semibold mb-4">{{ __('site.footer.contact_info') }}</h3>
            <ul class="text-sm text-stone-300">
                @if ($settings->phone)
                    <li><a href="{{ $settings->phone_href }}" class="inline-flex min-h-11 items-center hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal"><bdi dir="ltr">{{ $settings->phone }}</bdi></a></li>
                @endif
                @if ($settings->email)
                    <li><a href="mailto:{{ $settings->email }}" class="inline-flex min-h-11 items-center break-all hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal"><bdi>{{ $settings->email }}</bdi></a></li>
                @endif
                @if ($settings->address)
                    <li class="py-2">{{ $settings->address }}</li>
                @endif
                @if ($settings->working_hours)
                    <li class="pt-1 text-stone-400"><bdi>{{ $settings->working_hours }}</bdi></li>
                @endif
            </ul>
        </div>

        <div>
            <h3 class="text-base font-semibold mb-4">{{ __('site.footer.follow_us') }}</h3>
            @php
                $socialLabels = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'snapchat' => 'Snapchat', 'whatsapp' => 'WhatsApp'];
                $socialLinks = $settings->socialLinks() + array_filter(['whatsapp' => $settings->whatsapp_url]);
            @endphp
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($socialLinks as $network => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="h-11 w-11 flex items-center justify-center rounded-full bg-stone-700 hover:bg-stone-600 transition-colors focus-visible:ring-cream focus-visible:ring-offset-charcoal" aria-label="{{ $socialLabels[$network] }}">
                        <x-social-icon :network="$network" />
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="border-t border-stone-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-24 sm:pb-4 flex flex-col sm:flex-row flex-wrap items-center justify-center gap-x-4 gap-y-1 text-sm sm:text-xs text-stone-400 text-center">
            <span class="text-center">&copy; {{ now()->year }} {{ __('site.meta.site_name') }} — {{ __('site.footer.rights_reserved') }}</span>
            <span class="hidden sm:inline">&middot;</span>
            <a href="{{ route('legal.privacy') }}" class="inline-flex min-h-11 items-center hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.legal.privacy_policy_title') }}</a>
            <span class="hidden sm:inline">&middot;</span>
            <a href="{{ route('legal.terms') }}" class="inline-flex min-h-11 items-center hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.legal.terms_title') }}</a>
        </div>
    </div>
</footer>
