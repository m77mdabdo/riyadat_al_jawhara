{{-- Language switch on the sign-in and password pages (guests: saved in the session).
     Inline styles: these classes are not part of Filament's compiled stylesheet. --}}
<div style="display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: 1.25rem; font-size: .875rem;">
    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" style="color: #59554F; text-decoration: underline; text-underline-offset: 4px;">
        {{ __('Back to the website') }}
    </a>
    <div style="display: flex; gap: .25rem; padding: .2rem; border: 1px solid #E5E7EB; border-radius: .5rem;" role="group" aria-label="{{ __('Language') }}">
        @foreach (['ar' => 'العربية', 'en' => 'English'] as $code => $name)
            @php $active = app()->getLocale() === $code; @endphp
            <form method="POST" action="{{ route('admin.locale', $code) }}" style="margin: 0;">
                @csrf
                <button type="submit" lang="{{ $code }}" @if ($active) aria-current="true" @endif
                    style="border-radius: .375rem; padding: .3rem .75rem; font-weight: 600; cursor: pointer; {{ $active ? 'background: #2B2B2B; color: #fff;' : 'background: transparent; color: #403D39;' }}">{{ $name }}</button>
            </form>
        @endforeach
    </div>
</div>
