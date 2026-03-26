<?php

use App\Models\Profile;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('authenticated users without profile see onboarding', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('onboarding.show'));
    $response->assertOk();
});

test('can complete onboarding', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('onboarding.store'), [
        'full_name' => 'John Kila',
        'phone_number' => '+675 7123 4567',
        'preferred_language' => 'en',
        'monthly_income' => 3500.00,
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('profiles', [
        'user_id' => $user->id,
        'full_name' => 'John Kila',
        'preferred_language' => 'en',
    ]);
});

test('onboarding generates unique referral code', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->post(route('onboarding.store'), [
        'full_name' => 'Test User',
        'preferred_language' => 'en',
    ]);

    $profile = $user->fresh()->profile;
    expect($profile->referral_code)->toHaveLength(6);
});

test('validates required fields during onboarding', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('onboarding.store'), []);
    $response->assertSessionHasErrors(['full_name', 'preferred_language']);
});
