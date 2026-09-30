{{-- Local-only specimen of the type scale (see TypographyController). --}}
<x-layout title="Typography">
    <section class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2">
            @foreach (['ar' => ['dir' => 'rtl', 'h' => 'ريادة الجوهرة الإبداعية', 'p' => 'نورّد ونركّب الرخام والجرانيت والحجر الطبيعي للمشاريع السكنية والتجارية في مختلف مناطق المملكة، مع عناية بالتفاصيل من اختيار الخامة حتى التسليم.', 'n' => 'هاتف:'], 'en' => ['dir' => 'ltr', 'h' => 'Riyadat Al-Jawhara', 'p' => 'We supply and install marble, granite and natural stone for residential and commercial projects across the Kingdom, from choosing the slab to handover.', 'n' => 'Phone:']] as $lang => $t)
                <div lang="{{ $lang }}" dir="{{ $t['dir'] }}" class="rounded-xl border border-sand-200 bg-white p-6 space-y-5">
                    <p class="text-xs font-semibold text-stone-600">{{ strtoupper($lang) }} · font-display / body</p>
                    <h1 class="text-hero font-bold">{{ $t['h'] }}</h1>
                    <h2 class="text-page-title font-bold">{{ $t['h'] }}</h2>
                    <h3 class="text-section-title font-semibold">{{ $t['h'] }}</h3>
                    <h4 class="text-xl font-semibold">{{ $t['h'] }}</h4>
                    <p class="text-lg leading-prose">{{ $t['p'] }}</p>
                    <p class="text-base leading-prose">{{ $t['p'] }}</p>
                    <p class="text-sm leading-prose text-stone-600">{{ $t['p'] }}</p>
                    <p class="text-sm">{{ $t['n'] }} <bdi dir="ltr">+966 55 123 4567</bdi> · <bdi dir="ltr">info@example.com</bdi> · 2026 · 1,250 m²</p>
                    <p class="text-base"><strong>{{ $lang === 'ar' ? 'نص عريض' : 'Bold text' }}</strong> · <em>{{ $lang === 'ar' ? 'نص مائل (يظهر مستقيمًا)' : 'Italic text' }}</em> · <span class="font-medium">500</span> · <span class="font-semibold">600</span> · <span class="font-bold">700</span></p>
                </div>
            @endforeach
        </div>
    </section>
</x-layout>
