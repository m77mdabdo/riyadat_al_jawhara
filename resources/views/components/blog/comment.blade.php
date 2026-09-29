@props(['comment', 'post', 'depth' => 0])

@php
    $indentDepth = min($depth, 3);
@endphp

<div @class(['mt-4 border-s-2 border-stone-200 ps-4 sm:ps-6' => $indentDepth > 0])>
    <div class="rounded-lg border border-stone-200 bg-white p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <p class="font-semibold text-charcoal text-sm break-words min-w-0">{{ $comment->name }}</p>
            <p class="text-sm sm:text-xs text-stone-600 shrink-0"><bdi>{{ $comment->created_at->diffForHumans() }}</bdi></p>
        </div>
        <p class="mt-2 text-sm text-stone-700 leading-relaxed rtl:leading-loose break-words whitespace-pre-line">{{ $comment->body }}</p>

        <button
            type="button"
            @click="replyTo = replyTo === {{ $comment->id }} ? null : {{ $comment->id }}"
            :aria-expanded="(replyTo === {{ $comment->id }}).toString()"
            aria-controls="reply-{{ $comment->id }}"
            class="mt-2 inline-flex min-h-11 items-center text-sm sm:text-xs font-semibold text-charcoal hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-charcoal focus-visible:ring-offset-1 rounded"
        >
            {{ __('site.blog.comment_form_reply') }}
        </button>

        <div id="reply-{{ $comment->id }}" x-show="replyTo === {{ $comment->id }}" x-cloak class="mt-2">
            <p class="text-sm text-stone-600 mb-2">{{ __('site.blog.replying_to', ['name' => $comment->name]) }}</p>
            <x-blog.comment-form :post="$post" :parent-id="$comment->id" />
        </div>
    </div>

    @foreach ($comment->approvedReplies as $reply)
        <x-blog.comment :comment="$reply" :post="$post" :depth="$depth + 1" />
    @endforeach
</div>
