<?php

use App\Models\Subscription;
use App\Models\User;
use Tests\TestCase;

uses(TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

test('subscription casts starts_at to datetime', function () {
    $sub = Subscription::factory()->create();

    expect($sub->starts_at)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
});

test('subscription casts ends_at to datetime', function () {
    $sub = Subscription::factory()->create();

    expect($sub->ends_at)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
});

test('subscription belongs to user', function () {
    $sub = Subscription::factory()->create();

    expect($sub->user)->toBeInstanceOf(User::class);
});

test('isActive returns true for active status with future ends_at', function () {
    $sub = Subscription::factory()->create([
        'status' => 'active',
        'ends_at' => now()->addMonth(),
    ]);

    expect($sub->isActive())->toBeTrue();
});

test('isActive returns true for active status with null ends_at', function () {
    $sub = Subscription::factory()->create([
        'status' => 'active',
        'ends_at' => null,
    ]);

    expect($sub->isActive())->toBeTrue();
});

test('isActive returns false for cancelled status', function () {
    $sub = Subscription::factory()->create([
        'status' => 'cancelled',
        'ends_at' => now()->addMonth(),
    ]);

    expect($sub->isActive())->toBeFalse();
});

test('isActive returns false for expired status', function () {
    $sub = Subscription::factory()->expired()->create();

    expect($sub->isActive())->toBeFalse();
});

test('isActive returns false for active status with past ends_at', function () {
    $sub = Subscription::factory()->create([
        'status' => 'active',
        'ends_at' => now()->subDay(),
    ]);

    expect($sub->isActive())->toBeFalse();
});
