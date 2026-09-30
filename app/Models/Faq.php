<?php

namespace App\Models;

use App\Models\Concerns\HasActiveScope;
use App\Models\Concerns\HasLocalizedAttributes;
use App\Models\Concerns\LogsAdminActivity;
use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $question
 * @property-read string $answer
 */
class Faq extends Model
{
    /** @use HasFactory<FaqFactory> */
    use HasActiveScope, HasFactory, HasLocalizedAttributes, LogsAdminActivity;

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
}
