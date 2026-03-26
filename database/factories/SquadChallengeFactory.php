<?php

namespace Database\Factories;

use App\Models\Squad;
use App\Models\SquadChallenge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SquadChallenge>
 */
class SquadChallengeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'squad_id' => Squad::factory(),
            'name' => fake()->randomElement(['Save K100 this week', 'No betelnut for 7 days', 'Save K500 this month']),
            'target_amount' => fake()->randomFloat(2, 100, 1000),
            'starts_at' => now(),
            'ends_at' => now()->addDays(7),
            'is_active' => true,
        ];
    }
}
