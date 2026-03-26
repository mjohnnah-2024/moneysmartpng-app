<?php

namespace Database\Factories;

use App\Models\Streak;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Streak>
 */
class StreakFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['under_budget', 'daily_login', 'savings_contribution']),
            'current_count' => fake()->numberBetween(0, 30),
            'longest_count' => fake()->numberBetween(0, 60),
            'last_recorded_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
