<?php

namespace App\Http\Requests;

use App\Support\Sanitizer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category' => Sanitizer::text($this->input('category'), 50),
            'description' => Sanitizer::text($this->input('description'), 255),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'category' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date', 'before_or_equal:today'],
        ];
    }
}
