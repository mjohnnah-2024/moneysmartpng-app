<?php

use App\Models\ManualPayment;
use App\Models\Profile;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Profile::factory()->for($this->user)->create();
    $this->actingAs($this->user);
});

test('authenticated user can submit mobile money payment', function () {
    $response = $this->post(route('premium.mobile-money'), [
        'reference_number' => 'MM-12345678',
        'phone' => '+675 7123 4567',
        'months' => 1,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('manual_payments', [
        'user_id' => $this->user->id,
        'reference_number' => 'MM-12345678',
        'phone' => '+675 7123 4567',
        'months' => 1,
        'status' => 'pending',
    ]);
});

test('mobile money payment requires valid months', function () {
    $response = $this->post(route('premium.mobile-money'), [
        'reference_number' => 'MM-12345678',
        'phone' => '+675 7123 4567',
        'months' => 5, // Not in [1, 3, 6, 12]
    ]);

    $response->assertSessionHasErrors('months');
});

test('mobile money payment requires reference number', function () {
    $response = $this->post(route('premium.mobile-money'), [
        'reference_number' => '',
        'phone' => '+675 7123 4567',
        'months' => 1,
    ]);

    $response->assertSessionHasErrors('reference_number');
});

test('mobile money payment requires phone number', function () {
    $response = $this->post(route('premium.mobile-money'), [
        'reference_number' => 'MM-12345678',
        'phone' => '',
        'months' => 1,
    ]);

    $response->assertSessionHasErrors('phone');
});

test('mobile money payment amount is set based on plan pricing', function () {
    $this->post(route('premium.mobile-money'), [
        'reference_number' => 'MM-12345678',
        'phone' => '+675 7123 4567',
        'months' => 3,
    ]);

    $payment = ManualPayment::where('user_id', $this->user->id)->first();
    expect($payment->amount)->toBe('24.99');
});

test('guest cannot submit mobile money payment', function () {
    auth()->logout();

    $response = $this->post(route('premium.mobile-money'), [
        'reference_number' => 'MM-12345678',
        'phone' => '+675 7123 4567',
        'months' => 1,
    ]);

    $response->assertRedirect('/login');
});

test('manual payment factory creates valid records', function () {
    $payment = ManualPayment::factory()->for($this->user)->create();

    expect($payment->status)->toBe('pending');
    expect($payment->user_id)->toBe($this->user->id);
});

test('manual payment approved factory state works', function () {
    $payment = ManualPayment::factory()->for($this->user)->approved()->create();

    expect($payment->status)->toBe('approved');
});

test('manual payment rejected factory state works', function () {
    $payment = ManualPayment::factory()->for($this->user)->rejected()->create();

    expect($payment->status)->toBe('rejected');
    expect($payment->admin_notes)->not->toBeNull();
});
