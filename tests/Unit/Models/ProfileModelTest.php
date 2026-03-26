<?php

use App\Models\Profile;
use App\Models\User;
use Tests\TestCase;

uses(TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

test('profile casts monthly_income to decimal', function () {
    $profile = Profile::factory()->create(['monthly_income' => 5000]);

    expect($profile->monthly_income)->toBe('5000.00');
});

test('profile casts premium_days_earned to integer', function () {
    $profile = Profile::factory()->create(['premium_days_earned' => 30]);

    expect($profile->premium_days_earned)->toBeInt()->toBe(30);
});

test('profile defaults plan to free', function () {
    $profile = Profile::factory()->create();

    expect($profile->plan)->toBe('free');
});

test('profile defaults preferred_language to en', function () {
    $profile = Profile::factory()->create();

    expect($profile->preferred_language)->toBe('en');
});

test('profile defaults premium_days_earned to zero', function () {
    $profile = Profile::factory()->create();

    expect($profile->premium_days_earned)->toBe(0);
});

test('profile belongs to user', function () {
    $profile = Profile::factory()->create();

    expect($profile->user)->toBeInstanceOf(User::class);
});
