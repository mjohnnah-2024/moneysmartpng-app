<?php

namespace App\Http\Requests;

use App\Support\Sanitizer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManualPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reference_number' => Sanitizer::text($this->input('reference_number'), 100),
            'phone' => Sanitizer::text($this->input('phone'), 30),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reference_number' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'months' => ['required', 'integer', Rule::in([1, 3, 6, 12])],
        ];
    }
}
