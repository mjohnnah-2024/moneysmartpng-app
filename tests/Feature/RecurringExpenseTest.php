<?php

use App\Models\Profile;
use App\Models\RecurringExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Profile::factory()->for($this->user)->create();
    $this->actingAs($this->user);
});

test('can view bills index', function () {
    RecurringExpense::factory(3)->for($this->user)->create();

    $response = $this->get(route('bills.index'));
    $response->assertOk();
});

test('can view create bill page', function () {
    $response = $this->get(route('bills.create'));
    $response->assertOk();
});

test('can create a recurring bill', function () {
    $response = $this->post(route('bills.store'), [
        'name' => 'Rent',
        'amount' => 800.00,
        'category' => 'Rent',
        'frequency' => 'monthly',
        'due_day' => 15,
    ]);

    $response->assertRedirect(route('bills.index'));
    $this->assertDatabaseHas('recurring_expenses', [
        'user_id' => $this->user->id,
        'name' => 'Rent',
        'amount' => 800.00,
        'frequency' => 'monthly',
        'due_day' => 15,
    ]);
});

test('validates required fields when creating bill', function () {
    $response = $this->post(route('bills.store'), []);

    $response->assertSessionHasErrors(['name', 'amount', 'category', 'frequency', 'due_day']);
});

test('validates due_day bounds', function () {
    $response = $this->post(route('bills.store'), [
        'name' => 'Test',
        'amount' => 100,
        'category' => 'Rent',
        'frequency' => 'monthly',
        'due_day' => 32,
    ]);

    $response->assertSessionHasErrors(['due_day']);
});

test('validates frequency is valid enum', function () {
    $response = $this->post(route('bills.store'), [
        'name' => 'Test',
        'amount' => 100,
        'category' => 'Rent',
        'frequency' => 'daily',
        'due_day' => 15,
    ]);

    $response->assertSessionHasErrors(['frequency']);
});

test('can update a recurring bill', function () {
    $bill = RecurringExpense::factory()->for($this->user)->create();

    $response = $this->put(route('bills.update', $bill), [
        'name' => 'Updated Bill',
        'amount' => 1000.00,
        'category' => 'Utilities',
        'frequency' => 'monthly',
        'due_day' => 20,
    ]);

    $response->assertRedirect(route('bills.index'));
    $this->assertDatabaseHas('recurring_expenses', [
        'id' => $bill->id,
        'name' => 'Updated Bill',
        'amount' => 1000.00,
    ]);
});

test('can delete a recurring bill', function () {
    $bill = RecurringExpense::factory()->for($this->user)->create();

    $response = $this->delete(route('bills.destroy', $bill));

    $response->assertRedirect(route('bills.index'));
    $this->assertDatabaseMissing('recurring_expenses', ['id' => $bill->id]);
});

test('can mark bill as paid and creates transaction', function () {
    $bill = RecurringExpense::factory()->for($this->user)->create([
        'amount' => 500.00,
        'category' => 'Rent',
        'is_paid' => false,
    ]);

    $response = $this->post(route('bills.mark-paid', $bill));

    $response->assertRedirect(route('bills.index'));
    $this->assertDatabaseHas('recurring_expenses', [
        'id' => $bill->id,
        'is_paid' => true,
    ]);
    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'amount' => 500.00,
        'type' => 'expense',
        'category' => 'Rent',
    ]);
});

test('cannot update another users bill', function () {
    $otherUser = User::factory()->create();
    $bill = RecurringExpense::factory()->for($otherUser)->create();

    $response = $this->put(route('bills.update', $bill), [
        'name' => 'Hack',
        'amount' => 1,
        'category' => 'Other',
        'frequency' => 'monthly',
        'due_day' => 1,
    ]);

    $response->assertForbidden();
});

test('cannot delete another users bill', function () {
    $otherUser = User::factory()->create();
    $bill = RecurringExpense::factory()->for($otherUser)->create();

    $response = $this->delete(route('bills.destroy', $bill));

    $response->assertForbidden();
});
