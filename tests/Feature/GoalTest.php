<?php

use App\Models\Goal;
use App\Models\Profile;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Profile::factory()->for($this->user)->create();
    $this->actingAs($this->user);
});

test('can view goals index', function () {
    Goal::factory(3)->for($this->user)->create();

    $response = $this->get(route('goals.index'));
    $response->assertOk();
});

test('can filter goals by status', function () {
    Goal::factory()->for($this->user)->create(['status' => 'active']);
    Goal::factory()->for($this->user)->completed()->create();

    $response = $this->get(route('goals.index', ['status' => 'completed']));
    $response->assertOk();
});

test('can view create goal page', function () {
    $response = $this->get(route('goals.create'));
    $response->assertOk();
});

test('can create a goal', function () {
    $response = $this->post(route('goals.store'), [
        'name' => 'Emergency Fund',
        'target_amount' => 5000.00,
        'deadline' => now()->addMonths(6)->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('goals.index'));
    $this->assertDatabaseHas('goals', [
        'user_id' => $this->user->id,
        'name' => 'Emergency Fund',
        'target_amount' => 5000.00,
        'current_amount' => 0,
        'status' => 'active',
    ]);
});

test('can create a goal without deadline', function () {
    $response = $this->post(route('goals.store'), [
        'name' => 'New Phone',
        'target_amount' => 2000.00,
    ]);

    $response->assertRedirect(route('goals.index'));
    $this->assertDatabaseHas('goals', [
        'user_id' => $this->user->id,
        'name' => 'New Phone',
    ]);
});

test('validates required fields when creating goal', function () {
    $response = $this->post(route('goals.store'), []);

    $response->assertSessionHasErrors(['name', 'target_amount']);
});

test('can update a goal', function () {
    $goal = Goal::factory()->for($this->user)->create();

    $response = $this->put(route('goals.update', $goal), [
        'name' => 'Updated Goal Name',
        'target_amount' => 10000.00,
        'deadline' => now()->addYear()->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('goals.index'));
    $this->assertDatabaseHas('goals', [
        'id' => $goal->id,
        'name' => 'Updated Goal Name',
        'target_amount' => 10000.00,
    ]);
});

test('can delete a goal', function () {
    $goal = Goal::factory()->for($this->user)->create();

    $response = $this->delete(route('goals.destroy', $goal));

    $response->assertRedirect(route('goals.index'));
    $this->assertDatabaseMissing('goals', ['id' => $goal->id]);
});

test('can contribute to a goal', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'target_amount' => 1000,
        'current_amount' => 200,
    ]);

    $response = $this->post(route('goals.contribute', $goal), [
        'amount' => 100,
    ]);

    $response->assertRedirect(route('goals.index'));
    $this->assertDatabaseHas('goals', [
        'id' => $goal->id,
        'current_amount' => 300,
        'status' => 'active',
    ]);
});

test('goal completes automatically when target reached', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'target_amount' => 500,
        'current_amount' => 450,
    ]);

    $response = $this->post(route('goals.contribute', $goal), [
        'amount' => 50,
    ]);

    $response->assertRedirect(route('goals.index'));
    $this->assertDatabaseHas('goals', [
        'id' => $goal->id,
        'current_amount' => 500,
        'status' => 'completed',
    ]);
});

test('validates contribution amount', function () {
    $goal = Goal::factory()->for($this->user)->create();

    $response = $this->post(route('goals.contribute', $goal), [
        'amount' => 0,
    ]);

    $response->assertSessionHasErrors(['amount']);
});

test('cannot update another users goal', function () {
    $otherUser = User::factory()->create();
    $goal = Goal::factory()->for($otherUser)->create();

    $response = $this->put(route('goals.update', $goal), [
        'name' => 'Hacked Goal',
        'target_amount' => 100,
    ]);

    $response->assertForbidden();
});

test('cannot contribute to another users goal', function () {
    $otherUser = User::factory()->create();
    $goal = Goal::factory()->for($otherUser)->create();

    $response = $this->post(route('goals.contribute', $goal), [
        'amount' => 100,
    ]);

    $response->assertForbidden();
});

test('cannot delete another users goal', function () {
    $otherUser = User::factory()->create();
    $goal = Goal::factory()->for($otherUser)->create();

    $response = $this->delete(route('goals.destroy', $goal));

    $response->assertForbidden();
});
