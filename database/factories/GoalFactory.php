<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Goal>
 */
class GoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $target = fake()->randomFloat(2, 500, 20000);

        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement([
                'Emergency Fund',
                'School Fees',
                'New Phone',
                'Business Capital',
                'Church Donation',
                'Family Trip',
                'House Deposit',
            ]),
            'target_amount' => $target,
            'current_amount' => fake()->randomFloat(2, 0, $target * 0.7),
            'deadline' => fake()->dateTimeBetween('+1 month', '+12 months'),
            'status' => 'active',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'current_amount' => $attributes['target_amount'] ?? 5000,
        ]);
    }
}
