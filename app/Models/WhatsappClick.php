<?php

namespace App\Models;

use Database\Factories\WhatsappClickFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An anonymous click on a WhatsApp button (see App\Actions\Leads\RecordWhatsappClick).
 */
class WhatsappClick extends Model
{
    /** @use HasFactory<WhatsappClickFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'page',
        'locale',
        'context_type',
        'context_id',
        'lead_id',
    ];

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
