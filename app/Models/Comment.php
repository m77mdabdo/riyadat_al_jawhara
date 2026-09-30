<?php

namespace App\Models;

use App\Models\Concerns\LogsAdminActivity;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory, LogsAdminActivity;

    /** Audit moderation only; comments are created by visitors. */
    /** @var list<string> */
    protected array $activityLogOnly = ['status'];

    /** @var list<string> */
    protected static array $recordEvents = ['updated', 'deleted'];

    protected $fillable = [
        'post_id',
        'parent_id',
        'name',
        'email',
        'body',
        'status',
        'ip_address',
        'user_agent',
    ];

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /** @return BelongsTo<Post, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /** @return BelongsTo<Comment, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /** @return HasMany<Comment, $this> */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Replies that are safe to show publicly. Anything rendered on the site
     * must go through this relation -- `replies()` also returns pending and
     * spam comments and is only meant for the admin panel.
     *
     * @return HasMany<Comment, $this>
     */
    public function approvedReplies(): HasMany
    {
        return $this->replies()->where('status', 'approved');
    }
}
