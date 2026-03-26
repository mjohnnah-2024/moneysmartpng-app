<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
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
            'full_name' => fake()->name(),
            'phone_number' => fake()->numerify('+675 7### ####'),
            'preferred_language' => 'en',
            'monthly_income' => fake()->randomFloat(2, 500, 10000),
            'plan' => 'free',
            'referral_code' => strtoupper(Str::random(6)),
            'referred_by' => null,
            'premium_days_earned' => 0,
        ];
    }

    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => 'premium',
        ]);
    }

    public function tokPisin(): static
    {
        return $this->state(fn (array $attributes) => [
            'preferred_language' => 'tpi',
        ]);
    }
}
