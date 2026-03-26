<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category' => fake()->randomElement(Transaction::EXPENSE_CATEGORIES),
            'amount_limit' => fake()->randomFloat(2, 100, 2000),
            'month' => now()->startOfMonth(),
        ];
    }
}
