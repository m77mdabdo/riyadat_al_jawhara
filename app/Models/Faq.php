<?php

namespace App\Models;

use App\Models\Concerns\HasActiveScope;
use App\Models\Concerns\HasLocalizedAttributes;
use App\Models\Concerns\LogsAdminActivity;
use App\Models\Contracts\HasPublicUrl;
use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read string $question
 * @property-read string $answer
 */
class Faq extends Model implements HasPublicUrl
{
    /** @use HasFactory<FaqFactory> */
    use HasActiveScope, HasFactory, HasLocalizedAttributes, LogsAdminActivity, SoftDeletes;

    /**
     * Bilingual fields (see HasLocalizedAttributes): field => value when both languages are empty.
     *
     * @var array<string, string|null>
     */
    protected array $localized = [
        'question' => '',
        'answer' => '',
    ];

    protected $fillable = [
        'question_ar',
        'question_en',
        'answer_ar',
        'answer_en',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function publicUrl(?string $locale = null): ?string
    {
        return $this->is_active ? route('faq', ['locale' => $locale ?? app()->getLocale()]).'#faq-'.$this->id : null;
    }
}
