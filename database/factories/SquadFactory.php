<?php

namespace Database\Factories;

use App\Models\Squad;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Squad>
 */
class SquadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' Squad',
            'description' => fake()->sentence(),
            'creator_id' => User::factory(),
            'invite_code' => strtoupper(Str::random(8)),
            'max_members' => 10,
            'is_active' => true,
        ];
    }
}
