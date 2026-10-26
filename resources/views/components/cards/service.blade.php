@props(['service', 'headingTag' => 'h3', 'full' => false])

<a href="{{ route('services.show', $service) }}" {{ $attributes->class('group block rounded-xl overflow-hidden border border-stone-200 bg-white hover:border-sand-300 hover:shadow-lg hover:-translate-y-1 transition-all duration-300') }}>
    <div class="aspect-[4/3] bg-stone-200 overflow-hidden relative">
        <x-media-image :model="$service" :alt="$service->name" class="h-full w-full" img-class="group-hover:scale-105 transition-transform duration-700" />
        <div class="absolute bottom-3 start-3 h-10 w-10 rounded-lg bg-charcoal text-gold-400 flex items-center justify-center">
            <x-dynamic-component :component="$service->safe_icon" class="h-5 w-5" aria-hidden="true" />
        </div>
    </div>
    <div class="p-6">
        <{{ $headingTag }} class="font-semibold text-charcoal mb-2"><span class="link-sweep">{{ $service->name }}</span></{{ $headingTag }}>
        <p @class(['text-sm text-stone-600 break-words', 'line-clamp-2' => ! $full])>{{ $service->description }}</p>
        @if ($full)
            <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-charcoal group-hover:underline">{{ __('site.common.read_more') }} <x-heroicon-o-arrow-right class="h-4 w-4 rtl:rotate-180" aria-hidden="true" /></span>
        @endif
    </div>
</a>
