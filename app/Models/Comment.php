<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

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

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Replies that are safe to show publicly. Anything rendered on the site
     * must go through this relation -- `replies()` also returns pending and
     * spam comments and is only meant for the admin panel.
     */
    public function approvedReplies(): HasMany
    {
        return $this->replies()->where('status', 'approved');
    }
}
