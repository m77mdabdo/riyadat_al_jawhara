{{-- Home page below the hero. Rendered once per locale and cached (App\Support\FragmentCache); no per-visitor state or forms allowed here.
     Order and visibility: Website Content → Homepage (App\Support\HomeSections). --}}
@foreach (\App\Support\HomeSections::order() as $section)
    @include('home.sections.'.$section)
    @if ($section === 'categories')
        <x-motif.najdi-band />
    @endif
@endforeach
