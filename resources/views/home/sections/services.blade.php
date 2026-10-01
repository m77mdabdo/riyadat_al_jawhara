{{-- Services --}}
<section x-reveal class="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <x-section-heading :title="__('site.home.our_services')" :subtitle="__('site.home.our_services_subtitle')" class="mb-12" />
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($services as $service)
            <x-cards.service :service="$service" x-reveal style="--reveal-delay: {{ $loop->index * 90 }}ms" />
        @endforeach
    </div>
</section>
