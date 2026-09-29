<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $post = $this->route('post');

        return [
            'name' => ['required', 'string', 'max:255'],
            // not_regex: see StoreLeadRequest (Laravel 11 email-rule CRLF advisory).
            'email' => ['required', 'email', 'max:255', 'not_regex:/[\x00-\x1F\x7F]/'],
            'body' => ['required', 'string', 'max:2000'],
            'parent_id' => ['nullable', Rule::exists('comments', 'id')->where('post_id', $post->id)],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }
}
