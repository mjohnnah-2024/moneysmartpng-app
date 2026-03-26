<?php

namespace Database\Factories;

use App\Models\RecurringExpense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringExpense>
 */
class RecurringExpenseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Rent', 'School Fees', 'Utilities', 'Airtime', 'Church Donation']),
            'amount' => fake()->randomFloat(2, 50, 2000),
            'category' => fake()->randomElement(['Rent', 'School Fees', 'Utilities', 'Airtime/Data', 'Church/Community']),
            'frequency' => 'monthly',
            'due_day' => fake()->numberBetween(1, 28),
            'is_paid' => false,
            'last_paid_at' => null,
            'is_active' => true,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_paid' => true,
            'last_paid_at' => now(),
        ]);
    }
}
