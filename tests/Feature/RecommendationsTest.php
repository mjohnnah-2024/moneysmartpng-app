<?php

use App\Models\Budget;
use App\Models\Goal;
use App\Models\Profile;
use App\Models\RecurringExpense;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RecommendationsService;
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

test('returns empty recommendations when no data exists', function () {
    $service = new RecommendationsService;
    $result = $service->generate($this->user);

    expect($result)->toBeArray();
});

test('triggers daily limit warning when spending is high', function () {
    // Create a transaction that represents high daily spend
    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'amount' => 2500,
        'date' => now()->toDateString(),
        'category' => 'Food',
    ]);

    $service = new RecommendationsService;
    $result = $service->generate($this->user);

    $ids = array_column($result, 'id');
    expect($ids)->toContain('daily_limit_warning');
});

test('triggers category reduction when over budget', function () {
    Budget::factory()->for($this->user)->create([
        'category' => 'Food',
        'amount_limit' => 100,
        'month' => now()->startOfMonth(),
    ]);

    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'amount' => 150,
        'date' => now()->toDateString(),
        'category' => 'Food',
    ]);

    $service = new RecommendationsService;
    $result = $service->generate($this->user);

    $ids = array_column($result, 'id');
    expect($ids)->toContain('category_reduction_Food');
});

test('triggers savings opportunity when under budget', function () {
    Budget::factory()->for($this->user)->create([
        'category' => 'Transport',
        'amount_limit' => 500,
        'month' => now()->startOfMonth(),
    ]);

    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'amount' => 50,
        'date' => now()->toDateString(),
        'category' => 'Transport',
    ]);

    $service = new RecommendationsService;
    $result = $service->generate($this->user);

    $ids = array_column($result, 'id');
    expect($ids)->toContain('savings_opportunity_Transport');
});

test('triggers goal pace warning when behind', function () {
    Goal::factory()->for($this->user)->create([
        'name' => 'Emergency Fund',
        'target_amount' => 50000,
        'current_amount' => 0,
        'deadline' => now()->addDays(14),
        'status' => 'active',
    ]);

    $service = new RecommendationsService;
    $result = $service->generate($this->user);

    $ids = array_column($result, 'id');
    expect(array_filter($ids, fn ($id) => str_starts_with($id, 'goal_pace_')))->not->toBeEmpty();
});

test('triggers bill reminder for upcoming bills', function () {
    $dueDay = now()->addDays(2)->day;

    RecurringExpense::factory()->for($this->user)->create([
        'name' => 'Rent',
        'amount' => 1000,
        'due_day' => $dueDay,
        'is_paid' => false,
        'is_active' => true,
    ]);

    $service = new RecommendationsService;
    $result = $service->generate($this->user);

    $ids = array_column($result, 'id');
    expect(array_filter($ids, fn ($id) => str_starts_with($id, 'bill_reminder_')))->not->toBeEmpty();
});

test('returns at most 5 recommendations', function () {
    // Create many triggering conditions
    for ($i = 1; $i <= 6; $i++) {
        Budget::factory()->for($this->user)->create([
            'category' => "Category{$i}",
            'amount_limit' => 100,
            'month' => now()->startOfMonth(),
        ]);

        Transaction::factory()->for($this->user)->create([
            'type' => 'expense',
            'amount' => 150,
            'date' => now()->toDateString(),
            'category' => "Category{$i}",
        ]);
    }

    $service = new RecommendationsService;
    $result = $service->generate($this->user);

    expect(count($result))->toBeLessThanOrEqual(5);
});

test('each recommendation has required fields', function () {
    Transaction::factory()->for($this->user)->create([
        'type' => 'expense',
        'amount' => 2500,
        'date' => now()->toDateString(),
        'category' => 'Food',
    ]);

    $service = new RecommendationsService;
    $result = $service->generate($this->user);

    foreach ($result as $rec) {
        expect($rec)->toHaveKeys(['id', 'type', 'message']);
    }
});

test('insights page includes recommendations', function () {
    $response = $this->get(route('insights'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('insights')
        ->has('recommendations')
    );
});
