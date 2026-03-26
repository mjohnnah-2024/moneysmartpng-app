<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    private const EXPENSE_CATEGORIES = [
        'Food/Market',
        'Transport (PMV)',
        'Airtime/Data',
        'Utilities',
        'Church/Community',
        'School Fees',
        'Betelnut',
        'Housing',
        'Health',
        'Entertainment',
        'Other',
    ];

    private const INCOME_CATEGORIES = [
        'Salary',
        'Business',
        'Freelance',
        'Gift',
        'Other Income',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense']);

        return [
            'user_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 5, 2000),
            'type' => $type,
            'category' => fake()->randomElement(
                $type === 'income' ? self::INCOME_CATEGORIES : self::EXPENSE_CATEGORIES
            ),
            'description' => fake()->optional(0.7)->sentence(3),
            'date' => fake()->dateTimeBetween('-2 months', 'now'),
        ];
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
            'category' => fake()->randomElement(self::INCOME_CATEGORIES),
            'amount' => fake()->randomFloat(2, 500, 5000),
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
            'category' => fake()->randomElement(self::EXPENSE_CATEGORIES),
            'amount' => fake()->randomFloat(2, 5, 500),
        ]);
    }
}
