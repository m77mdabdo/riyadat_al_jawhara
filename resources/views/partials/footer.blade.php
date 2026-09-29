@php
    $settings = $settings ?? \App\Models\Setting::current();
@endphp

<footer class="bg-charcoal text-cream mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
        <div>
            <img src="{{ $settings->logoLightUrl() }}" alt="{{ __('site.meta.site_name') }}" class="h-12 w-auto mb-3">
            <p class="text-sm text-stone-300 leading-relaxed rtl:leading-loose">{{ __('site.meta.tagline') }}</p>
        </div>

        <div>
            <h3 class="text-base font-semibold mb-4">{{ __('site.footer.quick_links') }}</h3>
            <ul class="space-y-2 text-sm text-stone-300">
                <li><a href="{{ route('services.index') }}" class="hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.nav.services') }}</a></li>
                <li><a href="{{ route('stones.index') }}" class="hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.nav.stones') }}</a></li>
                <li><a href="{{ route('projects.index') }}" class="hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.nav.projects') }}</a></li>
                <li><a href="{{ route('blog.index') }}" class="hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.nav.blog') }}</a></li>
                <li><a href="{{ route('faq') }}" class="hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.nav.faq') }}</a></li>
                <li><a href="{{ route('about') }}" class="hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.nav.about') }}</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.nav.contact') }}</a></li>
            </ul>
        </div>

        <div>
            <h3 class="text-base font-semibold mb-4">{{ __('site.footer.contact_info') }}</h3>
            <ul class="space-y-2 text-sm text-stone-300">
                @if ($settings->phone)
                    <li><a href="{{ $settings->phone_href }}" class="hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal"><bdi dir="ltr">{{ $settings->phone }}</bdi></a></li>
                @endif
                @if ($settings->email)
                    <li><a href="mailto:{{ $settings->email }}" class="break-all hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal"><bdi>{{ $settings->email }}</bdi></a></li>
                @endif
                @if ($settings->address)
                    <li>{{ $settings->address }}</li>
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-4 text-xs text-stone-400 text-center">
            <span>&copy; {{ now()->year }} {{ __('site.meta.site_name') }} — {{ __('site.footer.rights_reserved') }}</span>
            <span class="hidden sm:inline">&middot;</span>
            <a href="{{ route('legal.privacy') }}" class="hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.legal.privacy_policy_title') }}</a>
            <span class="hidden sm:inline">&middot;</span>
            <a href="{{ route('legal.terms') }}" class="hover:text-cream transition-colors rounded-sm focus-visible:ring-cream focus-visible:ring-offset-charcoal">{{ __('site.legal.terms_title') }}</a>
        </div>
    </div>
</footer>
