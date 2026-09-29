<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'service_id',
        'stone_type_id',
        'project_id',
        'city',
        'message',
        'status',
        'assigned_to',
        'follow_up_at',
        'source',
        'locale',
        'source_url',
        'ip_address',
        'user_agent',
        'consented_at',
    ];

    protected $casts = [
        'consented_at' => 'datetime',
        'follow_up_at' => 'date',
    ];

    /**
     * @var array<string, string> status value => admin label
     */
    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'in_progress' => 'In Progress',
        'closed' => 'Closed',
    ];

    /**
     * @var array<string, string> source value => admin label
     */
    public const SOURCES = [
        'website' => 'Website',
        'whatsapp' => 'WhatsApp',
    ];

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', '!=', 'closed');
    }

    public function scopeFollowUpDue(Builder $query): Builder
    {
        return $query->open()->whereNotNull('follow_up_at')->whereDate('follow_up_at', '<=', today());
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function stoneType(): BelongsTo
    {
        return $this->belongsTo(StoneType::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class)->latest();
    }
}
