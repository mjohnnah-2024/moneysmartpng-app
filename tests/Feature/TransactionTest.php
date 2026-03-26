<?php

use App\Models\Profile;
use App\Models\Transaction;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Profile::factory()->for($this->user)->create();
    $this->actingAs($this->user);
});

test('can view transactions index', function () {
    Transaction::factory(3)->for($this->user)->create();

    $response = $this->get(route('transactions.index'));
    $response->assertOk();
});

test('can create a transaction', function () {
    $response = $this->post(route('transactions.store'), [
        'amount' => 150.00,
        'type' => 'expense',
        'category' => 'Food/Market',
        'description' => 'Weekly market shopping',
        'date' => now()->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('transactions.index'));
    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'amount' => 150.00,
        'type' => 'expense',
        'category' => 'Food/Market',
    ]);
});

test('validates required fields when creating transaction', function () {
    $response = $this->post(route('transactions.store'), []);

    $response->assertSessionHasErrors(['amount', 'type', 'category', 'date']);
});

test('can update a transaction', function () {
    $transaction = Transaction::factory()->for($this->user)->create();

    $response = $this->put(route('transactions.update', $transaction), [
        'amount' => 200.00,
        'type' => 'expense',
        'category' => 'Transport PMV',
        'description' => 'Bus fare',
        'date' => now()->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('transactions.index'));
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'amount' => 200.00,
        'category' => 'Transport PMV',
    ]);
});

test('can delete a transaction', function () {
    $transaction = Transaction::factory()->for($this->user)->create();

    $response = $this->delete(route('transactions.destroy', $transaction));

    $response->assertRedirect(route('transactions.index'));
    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
});

test('cannot update another users transaction', function () {
    $otherUser = User::factory()->create();
    $transaction = Transaction::factory()->for($otherUser)->create();

    $response = $this->put(route('transactions.update', $transaction), [
        'amount' => 200.00,
        'type' => 'expense',
        'category' => 'Food/Market',
        'date' => now()->format('Y-m-d'),
    ]);

    $response->assertForbidden();
});
