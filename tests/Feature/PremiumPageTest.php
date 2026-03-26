<?php

use App\Models\Profile;
use App\Models\Subscription;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Profile::factory()->for($this->user)->create();
    $this->actingAs($this->user);
});

test('premium page is accessible', function () {
    $response = $this->get(route('premium.show'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('premium'));
});

test('premium page shows pricing data', function () {
    $response = $this->get(route('premium.show'));

    $response->assertInertia(fn ($page) => $page
        ->has('pricing')
        ->has('currentPlan')
        ->has('stripeKey')
    );
});

test('premium page shows free plan for user without subscription', function () {
    $response = $this->get(route('premium.show'));

    $response->assertInertia(fn ($page) => $page
        ->where('currentPlan', 'free')
    );
});

test('premium page shows premium plan for subscribed user', function () {
    Subscription::factory()->for($this->user)->active()->create();

    $response = $this->get(route('premium.show'));

    $response->assertInertia(fn ($page) => $page
        ->where('currentPlan', 'premium')
        ->has('subscription')
    );
});

test('guest cannot access premium page', function () {
    auth()->logout();

    $response = $this->get(route('premium.show'));

    $response->assertRedirect('/login');
});

test('stripe checkout requires valid plan', function () {
    $response = $this->postJson(route('premium.checkout'), [
        'plan' => 'invalid',
    ]);

    $response->assertUnprocessable();
});

test('stripe webhook route exists', function () {
    $response = $this->post(route('stripe.webhook'), [], [
        'Stripe-Signature' => 'invalid',
    ]);

    // Should not be 404 (route exists), will be 400 or 500 due to invalid signature
    expect($response->status())->not->toBe(404);
});
