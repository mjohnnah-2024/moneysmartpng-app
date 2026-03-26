<?php

namespace Database\Factories;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationPreference>
 */
class NotificationPreferenceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['daily_limit_warning', 'category_reduction', 'bill_reminder', 'goal_pace_warning', 'streak_encouragement']),
            'in_app' => true,
            'push' => false,
            'enabled' => true,
        ];
    }
}
