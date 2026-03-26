<?php

use App\Models\Goal;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\UsageTracking;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Profile::factory()->for($this->user)->create();
    $this->actingAs($this->user);
});

test('free user can create transaction within limit', function () {
    $response = $this->post(route('transactions.store'), [
        'amount' => 10.00,
        'type' => 'expense',
        'category' => 'Food/Market',
        'description' => 'Test',
        'date' => now()->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('transactions.index'));
    $this->assertDatabaseHas('transactions', ['user_id' => $this->user->id]);
});

test('free user blocked after 50 transactions in a month', function () {
    UsageTracking::create([
        'user_id' => $this->user->id,
        'feature' => 'transactions',
        'period' => now()->format('Y-m'),
        'count' => 50,
    ]);

    $response = $this->post(route('transactions.store'), [
        'amount' => 10.00,
        'type' => 'expense',
        'category' => 'Food/Market',
        'date' => now()->format('Y-m-d'),
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('premium user bypasses transaction limit', function () {
    Subscription::factory()->for($this->user)->active()->create();

    UsageTracking::create([
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

test('free user blocked after 3 budgets in a month', function () {
    UsageTracking::create([
        'user_id' => $this->user->id,
        'feature' => 'budgets',
        'period' => now()->format('Y-m'),
        'count' => 3,
    ]);

    $response = $this->post(route('budgets.store'), [
        'category' => 'Food/Market',
        'amount_limit' => 500.00,
        'month' => now()->format('Y-m').'-01',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('free user blocked after 2 active goals', function () {
    Goal::factory(2)->for($this->user)->create(['status' => 'active']);

    $response = $this->post(route('goals.store'), [
        'name' => 'Third Goal',
        'target_amount' => 1000.00,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('free user can create goal when completed goals dont count', function () {
    Goal::factory()->for($this->user)->create(['status' => 'completed']);
    Goal::factory()->for($this->user)->create(['status' => 'active']);

    $response = $this->post(route('goals.store'), [
        'name' => 'Second Active Goal',
        'target_amount' => 1000.00,
    ]);

    $response->assertRedirect(route('goals.index'));
});

test('free user blocked after 20 ai messages', function () {
    UsageTracking::create([
        'user_id' => $this->user->id,
        'feature' => 'ai_chat',
        'period' => now()->format('Y-m'),
        'count' => 20,
    ]);

    $response = $this->postJson(route('chat.store'), [
        'message' => 'Hello',
    ]);

    $response->assertStatus(429);
    $response->assertJson(['upgrade' => true]);
});

test('usage tracking increments on transaction creation', function () {
    $this->post(route('transactions.store'), [
        'amount' => 10.00,
        'type' => 'expense',
        'category' => 'Food/Market',
        'date' => now()->format('Y-m-d'),
    ]);

    $this->assertDatabaseHas('usage_tracking', [
        'user_id' => $this->user->id,
        'feature' => 'transactions',
        'period' => now()->format('Y-m'),
    ]);
});

test('usage tracking increments on budget creation', function () {
    $this->post(route('budgets.store'), [
        'category' => 'Food/Market',
        'amount_limit' => 500.00,
        'month' => now()->format('Y-m').'-01',
    ]);

    $this->assertDatabaseHas('usage_tracking', [
        'user_id' => $this->user->id,
        'feature' => 'budgets',
        'period' => now()->format('Y-m'),
    ]);
});
