<?php

use App\Models\Budget;
use App\Models\ChatMessage;
use App\Models\Goal;
use App\Models\ManualPayment;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\UsageTracking;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('user casts is_admin to boolean', function () {
    $user = User::factory()->create(['is_admin' => 1]);

    expect($user->is_admin)->toBeTrue()->toBeBool();
});

test('user casts email_verified_at to datetime', function () {
    $user = User::factory()->create();

    expect($user->email_verified_at)->toBeInstanceOf(CarbonImmutable::class);
});

test('user casts password as hashed', function () {
    $user = User::factory()->create(['password' => 'plaintext123']);

    expect($user->password)->not->toBe('plaintext123');
    expect(password_verify('plaintext123', $user->password))->toBeTrue();
});

test('user has profile relationship', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create();

    expect($user->profile)->toBeInstanceOf(Profile::class);
});

test('user has transactions relationship', function () {
    $user = User::factory()->create();
    Transaction::factory()->count(3)->for($user)->create();

    expect($user->transactions)->toHaveCount(3);
    expect($user->transactions->first())->toBeInstanceOf(Transaction::class);
});

test('user has budgets relationship', function () {
    $user = User::factory()->create();
    Budget::factory()->for($user)->create(['category' => 'Food']);
    Budget::factory()->for($user)->create(['category' => 'Transport']);

    expect($user->budgets)->toHaveCount(2);
});

test('user has goals relationship', function () {
    $user = User::factory()->create();
    Goal::factory()->count(2)->for($user)->create();

    expect($user->goals)->toHaveCount(2);
});

test('user has chatMessages relationship', function () {
    $user = User::factory()->create();
    ChatMessage::factory()->count(4)->for($user)->create();

    expect($user->chatMessages)->toHaveCount(4);
});

test('user has usageTracking relationship', function () {
    $user = User::factory()->create();
    UsageTracking::factory()->for($user)->create();

    expect($user->usageTracking)->toHaveCount(1);
});

test('user has subscription relationship as latest of many', function () {
    $user = User::factory()->create();
    Subscription::factory()->for($user)->create(['created_at' => now()->subDay()]);
    $latest = Subscription::factory()->for($user)->create(['created_at' => now()]);

    expect($user->subscription->id)->toBe($latest->id);
});

test('user has manualPayments relationship', function () {
    $user = User::factory()->create();
    ManualPayment::factory()->count(2)->for($user)->create();

    expect($user->manualPayments)->toHaveCount(2);
});

test('hasActiveSubscription returns true with active subscription', function () {
    $user = User::factory()->create();
    Subscription::factory()->active()->for($user)->create();

    expect($user->hasActiveSubscription())->toBeTrue();
});

test('hasActiveSubscription returns false without subscription', function () {
    $user = User::factory()->create();

    expect($user->hasActiveSubscription())->toBeFalse();
});

test('hasActiveSubscription returns false with expired subscription', function () {
    $user = User::factory()->create();
    Subscription::factory()->expired()->for($user)->create();

    expect($user->hasActiveSubscription())->toBeFalse();
});

test('activePlan returns premium with active subscription', function () {
    $user = User::factory()->create();
    Subscription::factory()->active()->for($user)->create();

    expect($user->activePlan())->toBe('premium');
});

test('activePlan returns free without subscription', function () {
    $user = User::factory()->create();

    expect($user->activePlan())->toBe('free');
});

test('activePlan returns free with expired subscription', function () {
    $user = User::factory()->create();
    Subscription::factory()->expired()->for($user)->create();

    expect($user->activePlan())->toBe('free');
});
