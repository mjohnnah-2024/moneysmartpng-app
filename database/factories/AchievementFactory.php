<?php

namespace Database\Factories;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Achievement>
 */
class AchievementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'badge_key' => fake()->randomElement(['first_transaction', 'budget_creator', 'goal_setter', '3_day_streak', '7_day_streak']),
            'points' => fake()->randomElement([10, 15, 25, 30, 50]),
            'earned_at' => now(),
        ];
    }
}
