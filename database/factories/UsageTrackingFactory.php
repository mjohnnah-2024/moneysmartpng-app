<?php

namespace Database\Factories;

use App\Models\UsageTracking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UsageTracking>
 */
class UsageTrackingFactory extends Factory
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
            'feature' => fake()->randomElement(['transactions', 'goals', 'budgets', 'ai_messages']),
            'count' => fake()->numberBetween(0, 50),
            'period' => now()->format('Y-m'),
        ];
    }
}
