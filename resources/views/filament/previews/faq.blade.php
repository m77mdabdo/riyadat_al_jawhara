{{-- Admin preview: the question and answer as visitors read them, in both languages. --}}
<div style="display:grid;gap:1rem">
    @foreach (['ar' => __('Arabic'), 'en' => __('English')] as $locale => $language)
        <div dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $locale }}" style="border:1px solid #E0E0E0;border-radius:0.5rem;padding:1rem 1.25rem">
            <p style="font-size:0.75rem;opacity:0.6">{{ $language }}</p>
            <p style="font-weight:600;margin:0.25rem 0 0.5rem">{{ $record->getAttribute('question_'.$locale) ?: '—' }}</p>
            <p style="line-height:1.7;opacity:0.85;white-space:pre-line">{{ $record->getAttribute('answer_'.$locale) }}</p>
        </div>
    @endforeach
</div>
