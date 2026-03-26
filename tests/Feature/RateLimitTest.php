<?php

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Route;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Route::middleware(['web', 'auth', 'throttle:ai-chat'])
        ->post('/test-rate-limit', fn () => response()->json(['ok' => true]));
});

test('free user is rate limited after 5 requests per minute', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id, 'plan' => 'free']);

    for ($i = 0; $i < 5; $i++) {
        $this->actingAs($user)
            ->postJson('/test-rate-limit')
            ->assertOk();
    }

    $this->actingAs($user)
        ->postJson('/test-rate-limit')
        ->assertStatus(429);
});

test('premium user is rate limited after 10 requests per minute', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id, 'plan' => 'premium']);

    for ($i = 0; $i < 10; $i++) {
        $this->actingAs($user)
            ->postJson('/test-rate-limit')
            ->assertOk();
    }

    $this->actingAs($user)
        ->postJson('/test-rate-limit')
        ->assertStatus(429);
});

test('rate limit is per user not global', function () {
    $user1 = User::factory()->create();
    Profile::factory()->create(['user_id' => $user1->id, 'plan' => 'free']);

    $user2 = User::factory()->create();
    Profile::factory()->create(['user_id' => $user2->id, 'plan' => 'free']);

    // Exhaust user1 limit
    for ($i = 0; $i < 5; $i++) {
        $this->actingAs($user1)->postJson('/test-rate-limit');
    }

    $this->actingAs($user1)
        ->postJson('/test-rate-limit')
        ->assertStatus(429);

    // user2 should still be allowed
    $this->actingAs($user2)
        ->postJson('/test-rate-limit')
        ->assertOk();
});
