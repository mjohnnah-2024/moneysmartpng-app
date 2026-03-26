<?php

namespace Database\Factories;

use App\Models\SquadChallenge;
use App\Models\SquadChallengeProgress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SquadChallengeProgress>
 */
class SquadChallengeProgressFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'challenge_id' => SquadChallenge::factory(),
            'user_id' => User::factory(),
            'current_amount' => fake()->randomFloat(2, 0, 500),
        ];
    }
}
