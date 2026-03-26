<?php

use App\Models\ManualPayment;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    Profile::factory()->for($this->admin)->create();
});

test('admin can access payments index', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.payments.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/payments'));
});

test('non-admin cannot access payments index', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('admin.payments.index'))
        ->assertForbidden();
});

test('payments index shows pending count', function () {
    $user = User::factory()->create();
    ManualPayment::factory()->count(3)->for($user)->create(['status' => 'pending']);
    ManualPayment::factory()->approved()->for($user)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.payments.index'));

    $pendingCount = $response->getOriginalContent()->getData()['page']['props']['pendingCount'];
    expect($pendingCount)->toBe(3);
});

test('admin can approve a pending payment', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create(['plan' => 'free']);
    $payment = ManualPayment::factory()->for($user)->create([
        'status' => 'pending',
        'months' => 3,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.payments.approve', $payment))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($payment->fresh()->status)->toBe('approved');
    expect($user->fresh()->profile->plan)->toBe('premium');
    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $user->id,
        'status' => 'active',
        'plan' => 'premium',
    ]);
});

test('approving payment expires old subscriptions', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create(['plan' => 'premium']);
    $old = Subscription::factory()->active()->for($user)->create();
    $payment = ManualPayment::factory()->for($user)->create(['status' => 'pending', 'months' => 1]);

    $this->actingAs($this->admin)
        ->post(route('admin.payments.approve', $payment));

    expect($old->fresh()->status)->toBe('expired');
});

test('cannot approve a non-pending payment', function () {
    $user = User::factory()->create();
    $payment = ManualPayment::factory()->approved()->for($user)->create();

    $this->actingAs($this->admin)
        ->post(route('admin.payments.approve', $payment))
        ->assertRedirect()
        ->assertSessionHas('error');
});

test('admin can reject a pending payment with notes', function () {
    $user = User::factory()->create();
    $payment = ManualPayment::factory()->for($user)->create(['status' => 'pending']);

    $this->actingAs($this->admin)
        ->post(route('admin.payments.reject', $payment), [
            'admin_notes' => 'Could not verify reference number.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($payment->fresh()->status)->toBe('rejected');
    expect($payment->fresh()->admin_notes)->toBe('Could not verify reference number.');
});

test('rejecting a payment requires admin notes', function () {
    $user = User::factory()->create();
    $payment = ManualPayment::factory()->for($user)->create(['status' => 'pending']);

    $this->actingAs($this->admin)
        ->post(route('admin.payments.reject', $payment), [
            'admin_notes' => '',
        ])
        ->assertSessionHasErrors('admin_notes');
});

test('cannot reject a non-pending payment', function () {
    $user = User::factory()->create();
    $payment = ManualPayment::factory()->approved()->for($user)->create();

    $this->actingAs($this->admin)
        ->post(route('admin.payments.reject', $payment), [
            'admin_notes' => 'Test note',
        ])
        ->assertRedirect()
        ->assertSessionHas('error');
});
