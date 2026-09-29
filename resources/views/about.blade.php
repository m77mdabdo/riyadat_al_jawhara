<x-layout :title="__('site.about.title')" :description="$settings->about" :breadcrumbs="[['name' => __('site.about.title'), 'url' => route('about')]]">
    <x-page-header :title="__('site.about.title')" texture="texture-marble" />

    <section class="py-16 bg-white border-b border-stone-200">
        @if ($settings->about)
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-lg text-stone-700 leading-relaxed rtl:leading-loose">{{ $settings->about }}</p>
            </div>
        @endif
        @php
            $aboutBanner = \App\Support\SiteImages::url('about');
        @endphp
        <div @class(['max-w-5xl mx-auto px-4 sm:px-6 lg:px-8', 'mt-12' => filled($settings->about)])>
            <x-motif.mashrabiya-frame>
                <div class="aspect-[16/9] sm:aspect-[21/9] rounded-xl overflow-hidden">
                    <img src="{{ $aboutBanner ?? asset('images/about-hero.webp') }}" alt="{{ $aboutBanner ? __('site.about.banner_alt') : __('site.about.hero_alt') }}" width="1880" height="1253" loading="eager" decoding="async" class="h-full w-full object-cover">
                </div>
            </x-motif.mashrabiya-frame>
        </div>
    </section>

    @if ($settings->vision || $settings->mission)
        <section class="py-16 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 gap-8">
            @if ($settings->vision)
                <div class="rounded-xl border border-stone-200 bg-white p-8">
                    <div class="h-11 w-11 rounded-lg bg-charcoal text-gold-400 flex items-center justify-center mb-4">
                        <x-heroicon-o-eye class="h-5 w-5" aria-hidden="true" />
                    </div>
                    <h2 class="text-xl font-bold text-charcoal mb-2">{{ __('site.about.our_vision') }}</h2>
                    <p class="text-stone-600 leading-relaxed rtl:leading-loose">{{ $settings->vision }}</p>
                </div>
            @endif
            @if ($settings->mission)
                <div class="rounded-xl border border-stone-200 bg-white p-8">
                    <div class="h-11 w-11 rounded-lg bg-charcoal text-gold-400 flex items-center justify-center mb-4">
                        <x-heroicon-o-flag class="h-5 w-5" aria-hidden="true" />
                    </div>
                    <h2 class="text-xl font-bold text-charcoal mb-2">{{ __('site.about.our_mission') }}</h2>
                    <p class="text-stone-600 leading-relaxed rtl:leading-loose">{{ $settings->mission }}</p>
                </div>
            @endif
        </section>
    @endif

    @if ($teamMembers->isNotEmpty())
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <x-section-heading :title="__('site.about.our_team')" class="mb-12" />
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach ($teamMembers as $member)
                        <div class="text-center">
                            <div class="aspect-square rounded-full bg-stone-200 overflow-hidden mx-auto max-w-[160px]">
                                <x-media-image :model="$member" collection="photo" conversion="thumb" :alt="$member->name" sizes="160px" :width="300" :height="300" :placeholder-icon="false" class="h-full w-full" />
                            </div>
                            <h3 class="mt-4 font-semibold text-charcoal">{{ $member->name }}</h3>
                            <p class="text-sm text-stone-600">{{ $member->position }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layout>
