<?php

use App\Models\Profile;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Profile::factory()->for($this->user)->create();
});

// Authenticated GET routes return 200

test('authenticated user can access dashboard', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('authenticated user can access transactions index', function () {
    $this->actingAs($this->user)
        ->get(route('transactions.index'))
        ->assertOk();
});

test('authenticated user can access transactions create', function () {
    $this->actingAs($this->user)
        ->get(route('transactions.create'))
        ->assertOk();
});

test('authenticated user can access budgets index', function () {
    $this->actingAs($this->user)
        ->get(route('budgets.index'))
        ->assertOk();
});

test('authenticated user can access budgets create', function () {
    $this->actingAs($this->user)
        ->get(route('budgets.create'))
        ->assertOk();
});

test('authenticated user can access goals index', function () {
    $this->actingAs($this->user)
        ->get(route('goals.index'))
        ->assertOk();
});

test('authenticated user can access goals create', function () {
    $this->actingAs($this->user)
        ->get(route('goals.create'))
        ->assertOk();
});

test('authenticated user can access insights', function () {
    $this->actingAs($this->user)
        ->get(route('insights'))
        ->assertOk();
});

test('authenticated user can access chat', function () {
    $this->actingAs($this->user)
        ->get(route('chat.index'))
        ->assertOk();
});

test('authenticated user can access referral settings', function () {
    $this->actingAs($this->user)
        ->get(route('referral.index'))
        ->assertOk();
});

test('authenticated user can access language settings', function () {
    $this->actingAs($this->user)
        ->get(route('language.edit'))
        ->assertOk();
});

test('authenticated user can access premium page', function () {
    $this->actingAs($this->user)
        ->get(route('premium.show'))
        ->assertOk();
});

test('authenticated user can access subscription settings', function () {
    $this->actingAs($this->user)
        ->get(route('subscription.edit'))
        ->assertOk();
});

// Unauthenticated redirects

test('guest is redirected from dashboard', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

test('guest is redirected from transactions', function () {
    $this->get(route('transactions.index'))
        ->assertRedirect(route('login'));
});

test('guest is redirected from budgets', function () {
    $this->get(route('budgets.index'))
        ->assertRedirect(route('login'));
});

test('guest is redirected from goals', function () {
    $this->get(route('goals.index'))
        ->assertRedirect(route('login'));
});

test('guest is redirected from chat', function () {
    $this->get(route('chat.index'))
        ->assertRedirect(route('login'));
});

test('guest is redirected from admin dashboard', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

// Admin route protection

test('non-admin user cannot access admin dashboard', function () {
    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('non-admin user cannot access admin users', function () {
    $this->actingAs($this->user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('non-admin user cannot access admin payments', function () {
    $this->actingAs($this->user)
        ->get(route('admin.payments.index'))
        ->assertForbidden();
});

test('non-admin user cannot access admin ai-usage', function () {
    $this->actingAs($this->user)
        ->get(route('admin.ai-usage'))
        ->assertForbidden();
});

// Admin can access admin routes

test('admin user can access admin dashboard', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Profile::factory()->for($admin)->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

// Welcome page is public

test('welcome page is accessible without auth', function () {
    $this->get(route('home'))
        ->assertOk();
});

// User without profile gets redirected to onboarding

test('user without profile is redirected to onboarding', function () {
    $userNoProfile = User::factory()->create();

    $this->actingAs($userNoProfile)
        ->get(route('dashboard'))
        ->assertRedirect(route('onboarding.show'));
});

// CSRF protection

test('post without csrf token fails', function () {
    $this->actingAs($this->user)
        ->post(route('transactions.store'), [
            'amount' => 10.00,
            'type' => 'expense',
            'category' => 'Food/Market',
            'date' => now()->format('Y-m-d'),
        ])
        ->assertStatus(419);
})->skip('Pest test client includes CSRF token automatically');
