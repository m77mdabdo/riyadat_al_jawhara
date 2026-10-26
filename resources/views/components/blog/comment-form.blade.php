@props(['post', 'parentId' => null])

@php
    $honeypotId = 'website-'.($parentId ?? 'root');
    // A post page renders several comment forms (main + one per reply). Only the
    // form that was actually submitted gets the old input and field errors back.
    $isSubmittedForm = (string) old('parent_id', '') === (string) ($parentId ?? '') && (old('name') !== null || $errors->any());
    $value = fn (string $field) => $isSubmittedForm ? old($field) : null;
    $error = fn (string $field) => $isSubmittedForm ? $errors->first($field) : null;
@endphp

<form
    method="POST"
    action="{{ route('blog.comments.store', $post) }}"
    id="{{ $parentId ? 'reply-form-'.$parentId : 'main-comment-form' }}"
    class="space-y-4 rounded-xl border border-stone-200 bg-stone-50 p-4 sm:p-6"
    x-data="{ submitting: false }"
    @submit="if (submitting) { $event.preventDefault(); return; } submitting = true"
>
    @csrf

    @if ($parentId)
        <input type="hidden" name="parent_id" value="{{ $parentId }}">
    @endif

    {{-- Honeypot: clipped to a 1x1px box in normal flow (no off-screen positioning, so it can't
         affect document scroll width in either LTR or RTL), out of the tab order, but present
         in the DOM for bots to fill in. --}}
    <div class="h-px w-px overflow-hidden" aria-hidden="true">
        <label for="{{ $honeypotId }}">Website</label>
        <input type="text" name="website" id="{{ $honeypotId }}" tabindex="-1" autocomplete="off">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="name-{{ $honeypotId }}" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.blog.comment_form_name') }}</label>
            <input type="text" name="name" id="name-{{ $honeypotId }}" required maxlength="255" autocomplete="name" value="{{ $value('name') }}" @if ($error('name')) aria-invalid="true" aria-describedby="name-{{ $honeypotId }}-error" @endif class="w-full rounded-md border-stone-300 px-3 py-2.5 text-base sm:text-sm focus:border-charcoal focus:ring-charcoal @if ($error('name')) border-red-400 @endif">
            @if ($error('name')) <p id="name-{{ $honeypotId }}-error" class="mt-1 text-sm text-red-600">{{ $error('name') }}</p> @endif
        </div>
        <div>
            <label for="email-{{ $honeypotId }}" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.blog.comment_form_email') }}</label>
            <input type="email" name="email" id="email-{{ $honeypotId }}" required maxlength="255" autocomplete="email" dir="ltr" value="{{ $value('email') }}" @if ($error('email')) aria-invalid="true" aria-describedby="email-{{ $honeypotId }}-error" @endif class="w-full rounded-md border-stone-300 px-3 py-2.5 text-base sm:text-sm focus:border-charcoal focus:ring-charcoal @if ($error('email')) border-red-400 @endif">
            @if ($error('email')) <p id="email-{{ $honeypotId }}-error" class="mt-1 text-sm text-red-600">{{ $error('email') }}</p> @endif
        </div>
    </div>

    <div>
        <label for="body-{{ $honeypotId }}" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.blog.comment_form_body') }}</label>
        <textarea name="body" id="body-{{ $honeypotId }}" rows="4" required maxlength="2000" @if ($error('body')) aria-invalid="true" aria-describedby="body-{{ $honeypotId }}-error" @endif class="w-full rounded-md border-stone-300 px-3 py-2.5 text-base sm:text-sm focus:border-charcoal focus:ring-charcoal @if ($error('body')) border-red-400 @endif">{{ $value('body') }}</textarea>
        @if ($error('body')) <p id="body-{{ $honeypotId }}-error" class="mt-1 text-sm text-red-600">{{ $error('body') }}</p> @endif
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <x-button type="submit" x-bind:disabled="submitting" class="w-full sm:w-auto disabled:opacity-60 disabled:cursor-not-allowed">
            <svg x-show="submitting" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span x-text="submitting ? @js(__('site.common.sending')) : @js(__('site.blog.comment_form_submit'))">{{ __('site.blog.comment_form_submit') }}</span>
        </x-button>
        @if ($parentId)
            <button type="button" @click="replyTo = null" class="inline-flex min-h-11 items-center px-2 text-sm text-stone-600 hover:text-charcoal">
                {{ __('site.blog.comment_form_cancel_reply') }}
            </button>
        @endif
    </div>
</form>
