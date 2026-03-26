<?php

use App\Models\Budget;
use App\Models\User;
use Tests\TestCase;

uses(TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

test('budget casts amount_limit to decimal', function () {
    $budget = Budget::factory()->create(['amount_limit' => 500]);

    expect($budget->amount_limit)->toBe('500.00');
});

test('budget casts month to date', function () {
    $budget = Budget::factory()->create();

    expect($budget->month)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
});

test('budget belongs to user', function () {
    $budget = Budget::factory()->create();

    expect($budget->user)->toBeInstanceOf(User::class);
});

test('budget has correct fillable attributes', function () {
    $budget = Budget::factory()->create([
        'category' => 'Food/Market',
        'amount_limit' => 200.50,
    ]);

    expect($budget->category)->toBe('Food/Market');
    expect($budget->amount_limit)->toBe('200.50');
});
