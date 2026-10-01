{{-- Admin preview: the testimonial card in both languages (inline styles: the admin has no site CSS). --}}
@php($record->loadMissing("media"))
<div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(16rem,1fr))">
    @foreach (['ar' => __('Arabic'), 'en' => __('English')] as $locale => $language)
        <figure dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $locale }}" style="margin:0;border-radius:0.75rem;background:#2B2B2B;color:#F8F6F3;padding:1.25rem">
            <p style="font-size:0.75rem;opacity:0.7;margin-bottom:0.5rem">{{ $language }}</p>
            <p style="color:#C9A86A;letter-spacing:0.1em" aria-label="{{ $record->rating }}/5">{{ str_repeat('★', (int) $record->rating) }}{{ str_repeat('☆', 5 - (int) $record->rating) }}</p>
            <blockquote style="margin:0.75rem 0;line-height:1.7">“{{ $record->getAttribute('comment_'.$locale) ?: '—' }}”</blockquote>
            <figcaption style="display:flex;align-items:center;gap:0.5rem;font-weight:600">
                @if ($photo = $record->getFirstMediaUrl('client_photo'))
                    <img src="{{ $photo }}" alt="" style="width:2.25rem;height:2.25rem;border-radius:9999px;object-fit:cover">
                @endif
                {{ $record->client_name }}
            </figcaption>
        </figure>
    @endforeach
</div>
