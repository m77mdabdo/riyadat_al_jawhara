<x-layout :title="__('site.about.title')" :description="$settings->about">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-charcoal">{{ __('site.about.title') }}</h1>
            @if ($settings->about)
                <p class="mt-6 text-stone-600 leading-relaxed">{{ $settings->about }}</p>
            @endif
        </div>
    </section>

    @if ($settings->vision || $settings->mission)
        <section class="py-16 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 gap-8">
            @if ($settings->vision)
                <div class="rounded-xl border border-stone-200 bg-white p-8">
                    <div class="h-11 w-11 rounded-lg bg-charcoal flex items-center justify-center mb-4">
                        <x-heroicon-o-eye class="h-5 w-5 text-cream" />
                    </div>
                    <h2 class="text-xl font-bold text-charcoal mb-2">{{ __('site.about.our_vision') }}</h2>
                    <p class="text-stone-600 leading-relaxed">{{ $settings->vision }}</p>
                </div>
            @endif
            @if ($settings->mission)
                <div class="rounded-xl border border-stone-200 bg-white p-8">
                    <div class="h-11 w-11 rounded-lg bg-charcoal flex items-center justify-center mb-4">
                        <x-heroicon-o-flag class="h-5 w-5 text-cream" />
                    </div>
                    <h2 class="text-xl font-bold text-charcoal mb-2">{{ __('site.about.our_mission') }}</h2>
                    <p class="text-stone-600 leading-relaxed">{{ $settings->mission }}</p>
                </div>
            @endif
        </section>
    @endif

    @if ($teamMembers->isNotEmpty())
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-charcoal text-center mb-12">{{ __('site.about.our_team') }}</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach ($teamMembers as $member)
                        <div class="text-center">
                            <div class="aspect-square rounded-full bg-stone-200 overflow-hidden mx-auto max-w-[160px]">
                                @if ($member->getFirstMediaUrl('photo'))
                                    <img src="{{ $member->getFirstMediaUrl('photo', 'thumb') }}" alt="{{ $member->name }}" loading="lazy" class="h-full w-full object-cover">
                                @endif
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
