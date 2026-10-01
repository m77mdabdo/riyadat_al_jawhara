{{-- Admin preview: the team card on the About page, in both languages. --}}
@php($record->loadMissing("media"))
<div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(14rem,1fr))">
    @foreach (['ar' => __('Arabic'), 'en' => __('English')] as $locale => $language)
        <figure dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $locale }}" style="margin:0;border:1px solid #E0E0E0;border-radius:0.75rem;overflow:hidden;text-align:center">
            <div style="aspect-ratio:1;background:#EBE9E7">
                @if ($photo = $record->getFirstMediaUrl('photo'))
                    <img src="{{ $photo }}" alt="" style="width:100%;height:100%;object-fit:cover">
                @endif
            </div>
            <figcaption style="padding:1rem">
                <p style="font-size:0.75rem;opacity:0.6">{{ $language }}</p>
                <p style="font-weight:600">{{ $record->getAttribute('name_'.$locale) ?: '—' }}</p>
                <p style="font-size:0.875rem;opacity:0.75">{{ $record->getAttribute('position_'.$locale) }}</p>
            </figcaption>
        </figure>
    @endforeach
</div>
