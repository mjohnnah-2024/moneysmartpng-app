<?php

use App\Models\Goal;
use App\Models\User;
use Tests\TestCase;

uses(TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

test('goal casts target_amount to decimal', function () {
    $goal = Goal::factory()->create(['target_amount' => 1000]);

    expect($goal->target_amount)->toBe('1000.00');
});

test('goal casts current_amount to decimal', function () {
    $goal = Goal::factory()->create(['current_amount' => 250]);

    expect($goal->current_amount)->toBe('250.00');
});

test('goal casts deadline to date', function () {
    $goal = Goal::factory()->create(['deadline' => '2026-12-31']);

    expect($goal->deadline)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
});

test('goal defaults current_amount to zero', function () {
    $goal = new Goal;

    expect((float) $goal->current_amount)->toBe(0.0);
});

test('goal defaults status to active', function () {
    $goal = Goal::factory()->create();

    expect($goal->status)->toBe('active');
});

test('goal belongs to user', function () {
    $goal = Goal::factory()->create();

    expect($goal->user)->toBeInstanceOf(User::class);
});
