<?php

use App\Models\Profile;
use App\Models\Subscription;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Profile::factory()->for($this->user)->create();
    $this->actingAs($this->user);
});

test('user with active subscription has premium plan', function () {
    Subscription::factory()->for($this->user)->active()->create();

    expect($this->user->activePlan())->toBe('premium');
    expect($this->user->hasActiveSubscription())->toBeTrue();
});

test('user without subscription has free plan', function () {
    expect($this->user->activePlan())->toBe('free');
    expect($this->user->hasActiveSubscription())->toBeFalse();
});

test('user with expired subscription has free plan', function () {
    Subscription::factory()->for($this->user)->expired()->create();

    expect($this->user->fresh()->activePlan())->toBe('free');
});

test('user with cancelled subscription has free plan', function () {
    Subscription::factory()->for($this->user)->cancelled()->create();

    expect($this->user->fresh()->activePlan())->toBe('free');
});

test('subscription isActive returns false when ends_at is past', function () {
    $subscription = Subscription::factory()->for($this->user)->create([
        'status' => 'active',
        'ends_at' => now()->subDay(),
    ]);

    expect($subscription->isActive())->toBeFalse();
});

test('subscription isActive returns true when ends_at is future', function () {
    $subscription = Subscription::factory()->for($this->user)->active()->create();

    expect($subscription->isActive())->toBeTrue();
});

test('expire subscriptions command expires past-due subscriptions', function () {
    $subscription = Subscription::factory()->for($this->user)->create([
        'status' => 'active',
        'ends_at' => now()->subDay(),
    ]);

    $this->artisan('subscriptions:expire')
        ->expectsOutputToContain('Expired 1 subscription(s)')
        ->assertExitCode(0);

    expect($subscription->fresh()->status)->toBe('expired');
    expect($this->user->fresh()->profile->plan)->toBe('free');
});

test('expire subscriptions command does not expire future subscriptions', function () {
    $subscription = Subscription::factory()->for($this->user)->active()->create();

    $this->artisan('subscriptions:expire')
        ->expectsOutputToContain('Expired 0 subscription(s)')
        ->assertExitCode(0);

    expect($subscription->fresh()->status)->toBe('active');
});

test('plan gate uses subscription instead of profile plan', function () {
    // Profile says free
    $this->user->profile->update(['plan' => 'free']);

    // But has active subscription
    Subscription::factory()->for($this->user)->active()->create();

    // Should be able to exceed free limits
    \App\Models\UsageTracking::create([
        'user_id' => $this->user->id,
        'feature' => 'transactions',
        'period' => now()->format('Y-m'),
        'count' => 100,
    ]);

    $response = $this->post(route('transactions.store'), [
        'amount' => 10.00,
        'type' => 'expense',
        'category' => 'Food/Market',
        'date' => now()->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('transactions.index'));
});

test('settings subscription page is accessible', function () {
    $response = $this->get(route('subscription.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('settings/subscription'));
});

test('settings subscription page shows current plan', function () {
    $response = $this->get(route('subscription.edit'));

    $response->assertInertia(fn ($page) => $page
        ->has('currentPlan')
        ->has('usage')
        ->has('limits')
    );
});

test('settings subscription page shows premium for subscribed user', function () {
    Subscription::factory()->for($this->user)->active()->create();

    $response = $this->get(route('subscription.edit'));

    $response->assertInertia(fn ($page) => $page
        ->where('currentPlan', 'premium')
        ->has('subscription')
    );
});
