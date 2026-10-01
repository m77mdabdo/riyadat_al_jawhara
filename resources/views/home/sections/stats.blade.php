{{-- Stats: Settings-driven only, hidden when no figure is set --}}
@php $stats = $settings->stats(); @endphp
@if ($stats !== [])
    <section class="bg-charcoal text-cream py-16" aria-label="{{ __('site.home.stats_title') }}">
        <dl @class([
            'max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid gap-8 text-center',
            'grid-cols-2 lg:grid-cols-4' => count($stats) >= 3,
            'grid-cols-2' => count($stats) === 2,
            'grid-cols-1' => count($stats) === 1,
        ])>
            @foreach ($stats as $key => $value)
                <div class="flex flex-col-reverse" x-data="{ shown: {{ $value }}, target: {{ $value }} }"
                     x-init="if (! window.matchMedia('(prefers-reduced-motion: reduce)').matches) { shown = 0; const io = new IntersectionObserver((e) => { if (e[0].isIntersecting) { io.disconnect(); const t0 = performance.now(); const step = (t) => { const p = Math.min(1, (t - t0) / 1200); shown = Math.round(target * p); if (p < 1) requestAnimationFrame(step); }; requestAnimationFrame(step); } }); io.observe($el); }">
                    <dt class="mt-2 text-sm text-stone-300">{{ __('site.home.stats.'.$key) }}</dt>
                    <dd class="text-4xl font-bold text-gold-400 font-display"><bdi x-text="shown.toLocaleString('en-US')">{{ number_format($value) }}</bdi>+</dd>
                </div>
            @endforeach
        </dl>
    </section>
@endif
