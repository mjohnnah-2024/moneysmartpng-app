<?php

use App\Models\Profile;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('each profile generates a unique referral code', function () {
    $codes = collect();

    for ($i = 0; $i < 50; $i++) {
        $profile = Profile::factory()->create();
        $codes->push($profile->referral_code);
    }

    expect($codes->unique()->count())->toBe(50);
});

test('referral codes are 6 characters and uppercase', function () {
    $profile = Profile::factory()->create();

    expect($profile->referral_code)
        ->toHaveLength(6)
        ->toMatch('/^[A-Z0-9]+$/');
});

test('onboarding generates unique referral code for new user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.store'), [
            'full_name' => 'Test User',
            'phone_number' => '+675 7123 4567',
            'monthly_income' => 2000,
            'preferred_language' => 'en',
        ]);

    $profile = $user->fresh()->profile;
    expect($profile->referral_code)->not->toBeNull()
        ->toHaveLength(6);
});

test('two users onboarding get different referral codes', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $this->actingAs($user1)
        ->post(route('onboarding.store'), [
            'full_name' => 'User One',
            'phone_number' => '+675 7111 1111',
            'monthly_income' => 1000,
            'preferred_language' => 'en',
        ]);

    $this->actingAs($user2)
        ->post(route('onboarding.store'), [
            'full_name' => 'User Two',
            'phone_number' => '+675 7222 2222',
            'monthly_income' => 2000,
            'preferred_language' => 'en',
        ]);

    $code1 = $user1->fresh()->profile->referral_code;
    $code2 = $user2->fresh()->profile->referral_code;

    expect($code1)->not->toBe($code2);
});
