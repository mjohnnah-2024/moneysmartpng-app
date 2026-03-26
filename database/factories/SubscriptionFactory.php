<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
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
            'plan' => 'premium',
            'status' => 'active',
            'payment_method' => 'stripe',
            'stripe_subscription_id' => null,
            'stripe_customer_id' => null,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subMonth(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function mobileMoney(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'mobile_money',
            'stripe_subscription_id' => null,
            'stripe_customer_id' => null,
        ]);
    }

    public function stripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'stripe',
            'stripe_subscription_id' => 'sub_' . fake()->uuid(),
            'stripe_customer_id' => 'cus_' . fake()->uuid(),
        ]);
    }
}
