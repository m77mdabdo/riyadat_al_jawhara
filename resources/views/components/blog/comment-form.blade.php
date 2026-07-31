@props(['post', 'parentId' => null])

@php
    $honeypotId = 'website-'.($parentId ?? 'root');
@endphp

<form method="POST" action="{{ route('blog.comments.store', $post) }}" id="{{ $parentId ? 'reply-form-'.$parentId : 'main-comment-form' }}" class="space-y-4 rounded-xl border border-stone-200 bg-stone-50 p-6">
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
            <input type="text" name="name" id="name-{{ $honeypotId }}" required value="{{ old('name') }}" class="w-full rounded-md border-stone-300 px-3 py-2.5 text-sm focus:border-charcoal focus:ring-charcoal">
        </div>
        <div>
            <label for="email-{{ $honeypotId }}" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.blog.comment_form_email') }}</label>
            <input type="email" name="email" id="email-{{ $honeypotId }}" required value="{{ old('email') }}" class="w-full rounded-md border-stone-300 px-3 py-2.5 text-sm focus:border-charcoal focus:ring-charcoal">
        </div>
    </div>

    <div>
        <label for="body-{{ $honeypotId }}" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.blog.comment_form_body') }}</label>
        <textarea name="body" id="body-{{ $honeypotId }}" rows="4" required class="w-full rounded-md border-stone-300 px-3 py-2.5 text-sm focus:border-charcoal focus:ring-charcoal">{{ old('body') }}</textarea>
    </div>

    <div class="flex items-center gap-3">
        <x-button type="submit">{{ __('site.blog.comment_form_submit') }}</x-button>
        @if ($parentId)
            <button type="button" @click="replyTo = null" class="text-sm text-stone-600 hover:text-charcoal">
                {{ __('site.blog.comment_form_cancel_reply') }}
            </button>
        @endif
    </div>
</form>
