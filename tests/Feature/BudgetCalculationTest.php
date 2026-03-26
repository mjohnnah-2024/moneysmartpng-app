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

test('budget index sums spending per category', function () {
    $month = now()->format('Y-m');
    $monthDate = \Carbon\Carbon::parse($month . '-01');

    Budget::factory()->for($this->user)->create([
        'category' => 'Food/Market',
        'amount_limit' => 500,
        'month' => $monthDate,
    ]);

    Budget::factory()->for($this->user)->create([
        'category' => 'Transport PMV',
        'amount_limit' => 200,
        'month' => $monthDate,
    ]);

    // Spend in Food/Market
    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'category' => 'Food/Market',
        'amount' => 150,
        'date' => now(),
    ]);
    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'category' => 'Food/Market',
        'amount' => 75.50,
        'date' => now(),
    ]);

    // Spend in Transport PMV
    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'category' => 'Transport PMV',
        'amount' => 30,
        'date' => now(),
    ]);

    // Income should not count as spending
    Transaction::factory()->for($this->user)->create([
        'type' => 'income',
        'category' => 'Salary',
        'amount' => 3000,
        'date' => now(),
    ]);

    $response = $this->get(route('budgets.index', ['month' => $month]));
    $response->assertOk();

    $budgets = $response->getOriginalContent()->getData()['page']['props']['budgets'];

    $food = collect($budgets)->firstWhere('category', 'Food/Market');
    $transport = collect($budgets)->firstWhere('category', 'Transport PMV');

    expect($food['spent'])->toBe(225.5);
    expect($food['percentage'])->toBe(45.1);
    expect($transport['spent'])->toBe(30.0);
    expect($transport['percentage'])->toBe(15.0);
});

test('budget index shows correct total budget and total spent', function () {
    $monthDate = \Carbon\Carbon::parse(now()->format('Y-m') . '-01');

    Budget::factory()->for($this->user)->create([
        'category' => 'Food/Market',
        'amount_limit' => 500,
        'month' => $monthDate,
    ]);
    Budget::factory()->for($this->user)->create([
        'category' => 'Rent',
        'amount_limit' => 1000,
        'month' => $monthDate,
    ]);

    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'category' => 'Food/Market',
        'amount' => 200,
        'date' => now(),
    ]);
    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'category' => 'Rent',
        'amount' => 800,
        'date' => now(),
    ]);

    $response = $this->get(route('budgets.index'));
    $props = $response->getOriginalContent()->getData()['page']['props'];

    expect($props['totalBudget'])->toBe(1500.0);
    expect($props['totalSpent'])->toBe(1000.0);
});

test('budget calculation ignores spending from other months', function () {
    $monthDate = \Carbon\Carbon::parse(now()->format('Y-m') . '-01');

    Budget::factory()->for($this->user)->create([
        'category' => 'Food/Market',
        'amount_limit' => 500,
        'month' => $monthDate,
    ]);

    // Transaction in current month
    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'category' => 'Food/Market',
        'amount' => 100,
        'date' => now(),
    ]);

    // Transaction in previous month – should not be counted
    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'category' => 'Food/Market',
        'amount' => 999,
        'date' => now()->subMonth(),
    ]);

    $response = $this->get(route('budgets.index'));
    $budgets = $response->getOriginalContent()->getData()['page']['props']['budgets'];
    $food = collect($budgets)->firstWhere('category', 'Food/Market');

    expect($food['spent'])->toBe(100.0);
});

test('budget percentage is zero when amount_limit is zero', function () {
    $monthDate = \Carbon\Carbon::parse(now()->format('Y-m') . '-01');

    Budget::factory()->for($this->user)->create([
        'category' => 'Other',
        'amount_limit' => 0,
        'month' => $monthDate,
    ]);

    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'category' => 'Other',
        'amount' => 50,
        'date' => now(),
    ]);

    $response = $this->get(route('budgets.index'));
    $budgets = $response->getOriginalContent()->getData()['page']['props']['budgets'];
    $other = collect($budgets)->firstWhere('category', 'Other');

    expect((float) $other['percentage'])->toBe(0.0);
});
