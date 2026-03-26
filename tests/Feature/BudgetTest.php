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

test('can view budgets index', function () {
    Budget::factory(3)->for($this->user)->sequence(
        ['category' => 'Food/Market'],
        ['category' => 'Transport PMV'],
        ['category' => 'Utilities'],
    )->create();

    $response = $this->get(route('budgets.index'));
    $response->assertOk();
});

test('budgets index shows spending progress', function () {
    $month = now()->startOfMonth();
    Budget::factory()->for($this->user)->create([
        'category' => 'Food/Market',
        'amount_limit' => 500,
        'month' => $month,
    ]);

    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'category' => 'Food/Market',
        'amount' => 250,
        'date' => now(),
    ]);

    $response = $this->get(route('budgets.index'));
    $response->assertOk();
});

test('can view create budget page', function () {
    $response = $this->get(route('budgets.create'));
    $response->assertOk();
});

test('can create a budget', function () {
    $response = $this->post(route('budgets.store'), [
        'category' => 'Food/Market',
        'amount_limit' => 500.00,
        'month' => now()->startOfMonth()->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('budgets.index'));
    $this->assertDatabaseHas('budgets', [
        'user_id' => $this->user->id,
        'category' => 'Food/Market',
        'amount_limit' => 500.00,
    ]);
});

test('validates required fields when creating budget', function () {
    $response = $this->post(route('budgets.store'), []);

    $response->assertSessionHasErrors(['category', 'amount_limit', 'month']);
});

test('prevents duplicate budget for same category and month', function () {
    $month = now()->startOfMonth()->format('Y-m-d');
    Budget::factory()->for($this->user)->create([
        'category' => 'Food/Market',
        'month' => $month,
    ]);

    $response = $this->post(route('budgets.store'), [
        'category' => 'Food/Market',
        'amount_limit' => 300.00,
        'month' => $month,
    ]);

    $response->assertSessionHasErrors(['category']);
});

test('can update a budget', function () {
    $budget = Budget::factory()->for($this->user)->create();

    $response = $this->put(route('budgets.update', $budget), [
        'category' => $budget->category,
        'amount_limit' => 800.00,
        'month' => $budget->month->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('budgets.index'));
    $this->assertDatabaseHas('budgets', [
        'id' => $budget->id,
        'amount_limit' => 800.00,
    ]);
});

test('can delete a budget', function () {
    $budget = Budget::factory()->for($this->user)->create();

    $response = $this->delete(route('budgets.destroy', $budget));

    $response->assertRedirect(route('budgets.index'));
    $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
});

test('cannot update another users budget', function () {
    $otherUser = User::factory()->create();
    $budget = Budget::factory()->for($otherUser)->create();

    $response = $this->put(route('budgets.update', $budget), [
        'category' => $budget->category,
        'amount_limit' => 800.00,
        'month' => $budget->month->format('Y-m-d'),
    ]);

    $response->assertForbidden();
});

test('cannot delete another users budget', function () {
    $otherUser = User::factory()->create();
    $budget = Budget::factory()->for($otherUser)->create();

    $response = $this->delete(route('budgets.destroy', $budget));

    $response->assertForbidden();
});

test('can filter budgets by month', function () {
    Budget::factory()->for($this->user)->create([
        'month' => now()->startOfMonth(),
    ]);
    Budget::factory()->for($this->user)->create([
        'month' => now()->subMonth()->startOfMonth(),
        'category' => 'Transport PMV',
    ]);

    $response = $this->get(route('budgets.index', ['month' => now()->subMonth()->format('Y-m')]));
    $response->assertOk();
});
