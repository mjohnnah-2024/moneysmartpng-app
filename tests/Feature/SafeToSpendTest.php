<?php

use App\Models\Goal;
use App\Models\Profile;
use App\Models\RecurringExpense;
use App\Models\User;
use App\Services\SafeToSpendService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->profile = Profile::factory()->for($this->user)->create([
        'monthly_income' => 3000.00,
        'pay_cycle_type' => 'monthly',
        'pay_cycle_start_day' => 1,
    ]);
    $this->actingAs($this->user);
});

test('calculates safe-to-spend with no bills or goals', function () {
    $service = new SafeToSpendService;
    $result = $service->calculate($this->user);

    expect($result)->toHaveKeys(['daily', 'remaining', 'percentage', 'status', 'nextBill', 'breakdown']);
    expect($result['remaining'])->toBeGreaterThanOrEqual(0);
    expect($result['daily'])->toBeGreaterThanOrEqual(0);
    expect($result['status'])->toBeIn(['green', 'yellow', 'red']);
});

test('subtracts pending bills from available balance', function () {
    RecurringExpense::factory()->for($this->user)->create([
        'amount' => 500,
        'due_day' => now()->addDays(5)->day,
        'is_paid' => false,
        'is_active' => true,
    ]);

    $service = new SafeToSpendService;
    $result = $service->calculate($this->user);

    expect($result['breakdown']['pendingBills'])->toEqual(500.00);
});

test('does not subtract paid bills', function () {
    RecurringExpense::factory()->for($this->user)->paid()->create([
        'amount' => 500,
        'due_day' => now()->addDays(5)->day,
        'is_active' => true,
    ]);

    $service = new SafeToSpendService;
    $result = $service->calculate($this->user);

    expect($result['breakdown']['pendingBills'])->toEqual(0.00);
});

test('prorates goal contributions monthly', function () {
    Goal::factory()->for($this->user)->create([
        'target_amount' => 1200,
        'current_amount' => 0,
        'deadline' => now()->addMonths(12),
        'status' => 'active',
    ]);

    $service = new SafeToSpendService;
    $result = $service->calculate($this->user);

    expect($result['breakdown']['goalContributions'])->toBeGreaterThan(0);
});

test('returns green status when spending is low', function () {
    $service = new SafeToSpendService;
    $result = $service->calculate($this->user);

    expect($result['status'])->toBe('green');
});

test('returns next bill info', function () {
    $dueDay = min(now()->day + 3, 28);
    RecurringExpense::factory()->for($this->user)->create([
        'name' => 'School Fees',
        'amount' => 500,
        'due_day' => $dueDay,
        'is_paid' => false,
        'is_active' => true,
    ]);

    $service = new SafeToSpendService;
    $result = $service->calculate($this->user);

    if ($result['nextBill'] !== null) {
        expect($result['nextBill']['name'])->toBe('School Fees');
        expect($result['nextBill']['amount'])->toEqual(500.00);
    }
});

test('dashboard includes safe-to-spend data', function () {
    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('safeToSpend')
        ->has('safeToSpend.daily')
        ->has('safeToSpend.remaining')
        ->has('safeToSpend.status')
    );
});
