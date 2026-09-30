<?php

namespace App\Actions\Comments;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Notifications\CommentPendingNotification;
use App\Support\VisitorContext;
use Illuminate\Support\Str;

/**
 * Stores a visitor comment or reply for moderation.
 */
class StoreComment
{
    /**
     * @param  array<string, mixed>  $data  Validated StoreCommentRequest data
     */
    public function handle(Post $post, array $data, VisitorContext $visitor): Comment
    {
        // Honeypot: bots that fill this field are silently marked as spam,
        // not rejected, so they don't learn the submission was caught.
        $isSpam = filled($data['website'] ?? null);

        $comment = Comment::create([
            'post_id' => $post->id,
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'body' => $data['body'],
            'status' => ($isSpam ? CommentStatus::Spam : CommentStatus::Pending)->value,
            'ip_address' => $visitor->ip,
            // comments.user_agent is VARCHAR(255): MySQL in strict mode rejects longer values.
            'user_agent' => $visitor->userAgent !== null ? Str::limit($visitor->userAgent, 255, '') : null,
        ]);

        CommentPendingNotification::dispatchFor($comment);

        return $comment;
    }
}
