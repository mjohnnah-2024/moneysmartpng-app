<?php

use App\Models\Budget;
use App\Models\Goal;
use App\Models\Profile;
use App\Models\Transaction;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->create();
    Profile::factory()->for($this->owner)->create();

    $this->otherUser = User::factory()->create();
    Profile::factory()->for($this->otherUser)->create();
});

// Transaction Policy Tests

test('user cannot edit another users transaction', function () {
    $transaction = Transaction::factory()->for($this->owner)->create();

    $this->actingAs($this->otherUser)
        ->get(route('transactions.edit', $transaction))
        ->assertForbidden();
});

test('user cannot update another users transaction', function () {
    $transaction = Transaction::factory()->for($this->owner)->create();

    $this->actingAs($this->otherUser)
        ->put(route('transactions.update', $transaction), [
            'amount' => 99.99,
            'type' => 'expense',
            'category' => 'Food/Market',
            'date' => now()->format('Y-m-d'),
        ])
        ->assertForbidden();
});

test('user cannot delete another users transaction', function () {
    $transaction = Transaction::factory()->for($this->owner)->create();

    $this->actingAs($this->otherUser)
        ->delete(route('transactions.destroy', $transaction))
        ->assertForbidden();
});

test('user can edit own transaction', function () {
    $transaction = Transaction::factory()->for($this->owner)->create();

    $this->actingAs($this->owner)
        ->get(route('transactions.edit', $transaction))
        ->assertSuccessful();
});

// Budget Policy Tests

test('user cannot edit another users budget', function () {
    $budget = Budget::factory()->for($this->owner)->create();

    $this->actingAs($this->otherUser)
        ->get(route('budgets.edit', $budget))
        ->assertForbidden();
});

test('user cannot delete another users budget', function () {
    $budget = Budget::factory()->for($this->owner)->create();

    $this->actingAs($this->otherUser)
        ->delete(route('budgets.destroy', $budget))
        ->assertForbidden();
});

// Goal Policy Tests

test('user cannot edit another users goal', function () {
    $goal = Goal::factory()->for($this->owner)->create();

    $this->actingAs($this->otherUser)
        ->get(route('goals.edit', $goal))
        ->assertForbidden();
});

test('user cannot contribute to another users goal', function () {
    $goal = Goal::factory()->for($this->owner)->create();

    $this->actingAs($this->otherUser)
        ->post(route('goals.contribute', $goal), ['amount' => 10.00])
        ->assertForbidden();
});

test('user cannot delete another users goal', function () {
    $goal = Goal::factory()->for($this->owner)->create();

    $this->actingAs($this->otherUser)
        ->delete(route('goals.destroy', $goal))
        ->assertForbidden();
});

// Data scope tests

test('user only sees own transactions in index', function () {
    Transaction::factory()->for($this->owner)->count(3)->create();
    Transaction::factory()->for($this->otherUser)->count(2)->create();

    $response = $this->actingAs($this->owner)
        ->get(route('transactions.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('transactions/index')
        ->has('transactions.data', 3)
    );
});

test('user only sees own goals in index', function () {
    Goal::factory()->for($this->owner)->count(2)->create();
    Goal::factory()->for($this->otherUser)->count(3)->create();

    $response = $this->actingAs($this->owner)
        ->get(route('goals.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('goals/index')
        ->has('goals', 2)
    );
});
