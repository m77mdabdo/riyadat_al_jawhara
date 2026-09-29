<?php

namespace App\Models;

use App\Models\Concerns\HasActiveScope;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasActiveScope, HasTranslations;

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

    public function getQuestionAttribute(): string
    {
        return $this->translated('question') ?? '';
    }

    public function getAnswerAttribute(): string
    {
        return $this->translated('answer') ?? '';
    }
}
