<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\s\-()]{7,30}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            // Only records currently shown on the site may be referenced.
            'service_id' => ['nullable', Rule::exists('services', 'id')->where('is_active', true)],
            'stone_type_id' => ['nullable', Rule::exists('stone_types', 'id')->where('is_active', true)],
            'project_id' => ['nullable', Rule::exists('projects', 'id')->where('is_active', true)],
            'message' => ['required', 'string', 'max:2000'],
            'consent' => ['accepted'],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Arabic keyboards type Arabic-Indic (٠-٩) or Persian (۰-۹) digits;
     * normalize them so the phone rule and staff see plain 0-9.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('phone'))) {
            $this->merge([
                'phone' => trim(strtr($this->input('phone'), [
                    '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
                    '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
                    '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
                    '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
                ])),
            ]);
        }
    }

    public function attributes(): array
    {
        return [
            'consent' => __('site.contact.consent_attribute'),
        ];
    }
}
