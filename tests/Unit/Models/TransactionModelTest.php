<?php

use App\Models\Transaction;
use App\Models\User;
use Tests\TestCase;

uses(TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

test('transaction casts amount to decimal', function () {
    $txn = Transaction::factory()->create(['amount' => 100]);

    expect($txn->amount)->toBe('100.00');
});

test('transaction casts date to date', function () {
    $txn = Transaction::factory()->create();

    expect($txn->date)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
});

test('transaction belongs to user', function () {
    $txn = Transaction::factory()->create();

    expect($txn->user)->toBeInstanceOf(User::class);
});

test('transaction defines expense categories', function () {
    expect(Transaction::EXPENSE_CATEGORIES)->toBeArray()
        ->toContain('Food/Market')
        ->toContain('Transport PMV')
        ->toContain('Airtime/Data')
        ->toContain('Betelnut');
});

test('transaction defines income categories', function () {
    expect(Transaction::INCOME_CATEGORIES)->toBeArray()
        ->toContain('Salary')
        ->toContain('Business')
        ->toContain('Freelance');
});
