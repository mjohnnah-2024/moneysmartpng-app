<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $month = Carbon::parse($this->input('month'))->startOfMonth();

        return [
            'category' => [
                'required',
                'string',
                Rule::in(Transaction::EXPENSE_CATEGORIES),
                Rule::unique('budgets')->where(fn ($query) => $query
                    ->where('user_id', $this->user()->id)
                    ->where('month', $month)
                ),
            ],
            'amount_limit' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'month' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category.unique' => 'You already have a budget for this category this month.',
        ];
    }
}
