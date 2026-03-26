<?php

namespace App\Http\Requests;

use App\Support\Sanitizer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => Sanitizer::text($this->input('full_name'), 255),
            'phone_number' => Sanitizer::text($this->input('phone_number'), 20),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'preferred_language' => ['required', Rule::in(['en', 'tpi'])],
            'monthly_income' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'referred_by' => ['nullable', 'string', 'size:6', Rule::exists('profiles', 'referral_code')],
        ];
    }
}
