<?php

namespace Database\Factories;

use App\Models\ManualPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ManualPayment>
 */
class ManualPaymentFactory extends Factory
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
            'reference_number' => 'MM-' . fake()->numerify('########'),
            'phone' => fake()->numerify('+675 7### ####'),
            'months' => fake()->randomElement([1, 3, 6, 12]),
            'amount' => fake()->randomFloat(2, 9.99, 99.99),
            'status' => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'admin_notes' => 'Payment could not be verified.',
        ]);
    }
}
