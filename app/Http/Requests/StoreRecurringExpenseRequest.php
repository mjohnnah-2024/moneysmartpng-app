<?php

namespace App\Http\Requests;

use App\Support\Sanitizer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecurringExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => Sanitizer::text($this->input('name'), 255),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'category' => ['required', 'string', 'max:100'],
            'frequency' => ['required', Rule::in(['monthly', 'fortnightly', 'weekly'])],
            'due_day' => ['required', 'integer', 'min:1', 'max:31'],
        ];
    }
}
