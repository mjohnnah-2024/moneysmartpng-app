<?php

use App\Models\Profile;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can view referral page', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create();
    $this->actingAs($user);

    $response = $this->get(route('referral.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('settings/referral')
        ->has('referralCode')
        ->has('referralCount')
        ->has('premiumDaysEarned')
        ->has('referralLink')
    );
});

test('referral count reflects referred users', function () {
    $referrer = User::factory()->create();
    $referrerProfile = Profile::factory()->for($referrer)->create([
        'referral_code' => 'ABC123',
    ]);

    // Create 3 users referred by this code
    foreach (range(1, 3) as $i) {
        $u = User::factory()->create();
        Profile::factory()->for($u)->create([
            'referred_by' => 'ABC123',
        ]);
    }

    $this->actingAs($referrer);

    $response = $this->get(route('referral.index'));

    $response->assertInertia(fn ($page) => $page
        ->where('referralCount', 3)
    );
});

test('referral credits referrer with 30 premium days during onboarding', function () {
    $referrer = User::factory()->create();
    Profile::factory()->for($referrer)->create([
        'referral_code' => 'REF001',
        'premium_days_earned' => 0,
    ]);

    $newUser = User::factory()->create();
    $this->actingAs($newUser);

    $this->post(route('onboarding.store'), [
        'full_name' => 'New User',
        'preferred_language' => 'en',
        'referred_by' => 'REF001',
    ]);

    expect($referrer->fresh()->profile->premium_days_earned)->toBe(30);
});

test('invalid referral code is rejected during onboarding', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('onboarding.store'), [
        'full_name' => 'New User',
        'preferred_language' => 'en',
        'referred_by' => 'INVALID',
    ]);

    $response->assertSessionHasErrors('referred_by');
});

test('onboarding without referral code still works', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('onboarding.store'), [
        'full_name' => 'New User',
        'preferred_language' => 'en',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('profiles', [
        'user_id' => $user->id,
        'referred_by' => null,
    ]);
});
