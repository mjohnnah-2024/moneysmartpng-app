<?php

namespace Database\Factories;

use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SquadMember>
 */
class SquadMemberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'squad_id' => Squad::factory(),
            'user_id' => User::factory(),
            'role' => 'member',
            'joined_at' => now(),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }
}
