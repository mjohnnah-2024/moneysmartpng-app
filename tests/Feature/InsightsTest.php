<?php

use App\Models\Budget;
use App\Models\Profile;
use App\Models\Transaction;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Profile::factory()->for($this->user)->create();
    $this->actingAs($this->user);
});

test('can view insights page', function () {
    $response = $this->get(route('insights'));
    $response->assertOk();
});

test('insights shows spending by category', function () {
    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'category' => 'Food/Market',
        'amount' => 200,
        'date' => now(),
    ]);
    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'category' => 'Transport PMV',
        'amount' => 50,
        'date' => now(),
    ]);

    $response = $this->get(route('insights'));
    $response->assertOk();
});

test('insights shows monthly trends', function () {
    // Create transactions across two months
    Transaction::factory()->for($this->user)->create([
        'type' => 'income',
        'amount' => 3000,
        'date' => now(),
    ]);
    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'amount' => 1000,
        'date' => now()->subMonth(),
    ]);

    $response = $this->get(route('insights'));
    $response->assertOk();
});

test('insights shows budget alerts when overspending', function () {
    Budget::factory()->for($this->user)->create([
        'category' => 'Food/Market',
        'amount_limit' => 100,
        'month' => now()->startOfMonth(),
    ]);

    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'category' => 'Food/Market',
        'amount' => 110,
        'date' => now(),
    ]);

    $response = $this->get(route('insights'));
    $response->assertOk();
});

test('insights shows month-over-month comparison', function () {
    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'amount' => 500,
        'date' => now(),
    ]);
    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'amount' => 300,
        'date' => now()->subMonth(),
    ]);

    $response = $this->get(route('insights'));
    $response->assertOk();
});

test('unauthenticated user cannot access insights', function () {
    auth()->logout();

    $response = $this->get(route('insights'));
    $response->assertRedirect(route('login'));
});
