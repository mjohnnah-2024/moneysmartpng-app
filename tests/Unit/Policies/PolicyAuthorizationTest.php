<?php

use App\Models\Budget;
use App\Models\ChatMessage;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\UsageTracking;
use App\Models\User;
use App\Policies\BudgetPolicy;
use App\Policies\ChatMessagePolicy;
use App\Policies\GoalPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UsageTrackingPolicy;
use Tests\TestCase;

uses(TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

// TransactionPolicy

test('transaction policy allows owner to view', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->create();

    expect((new TransactionPolicy)->view($user, $transaction))->toBeTrue();
});

test('transaction policy denies non-owner view', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $transaction = Transaction::factory()->for($owner)->create();

    expect((new TransactionPolicy)->view($other, $transaction))->toBeFalse();
});

test('transaction policy allows owner to update', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->create();

    expect((new TransactionPolicy)->update($user, $transaction))->toBeTrue();
});

test('transaction policy denies non-owner update', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $transaction = Transaction::factory()->for($owner)->create();

    expect((new TransactionPolicy)->update($other, $transaction))->toBeFalse();
});

test('transaction policy allows owner to delete', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->create();

    expect((new TransactionPolicy)->delete($user, $transaction))->toBeTrue();
});

test('transaction policy denies non-owner delete', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $transaction = Transaction::factory()->for($owner)->create();

    expect((new TransactionPolicy)->delete($other, $transaction))->toBeFalse();
});

// BudgetPolicy

test('budget policy allows owner to view', function () {
    $user = User::factory()->create();
    $budget = Budget::factory()->for($user)->create();

    expect((new BudgetPolicy)->view($user, $budget))->toBeTrue();
});

test('budget policy denies non-owner view', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $budget = Budget::factory()->for($owner)->create();

    expect((new BudgetPolicy)->view($other, $budget))->toBeFalse();
});

test('budget policy allows owner to update', function () {
    $user = User::factory()->create();
    $budget = Budget::factory()->for($user)->create();

    expect((new BudgetPolicy)->update($user, $budget))->toBeTrue();
});

test('budget policy denies non-owner delete', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $budget = Budget::factory()->for($owner)->create();

    expect((new BudgetPolicy)->delete($other, $budget))->toBeFalse();
});

// GoalPolicy

test('goal policy allows owner to view', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create();

    expect((new GoalPolicy)->view($user, $goal))->toBeTrue();
});

test('goal policy denies non-owner view', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $goal = Goal::factory()->for($owner)->create();

    expect((new GoalPolicy)->view($other, $goal))->toBeFalse();
});

test('goal policy allows owner to update', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create();

    expect((new GoalPolicy)->update($user, $goal))->toBeTrue();
});

test('goal policy denies non-owner delete', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $goal = Goal::factory()->for($owner)->create();

    expect((new GoalPolicy)->delete($other, $goal))->toBeFalse();
});

// ChatMessagePolicy

test('chat message policy allows owner to view', function () {
    $user = User::factory()->create();
    $message = ChatMessage::factory()->for($user)->create();

    expect((new ChatMessagePolicy)->view($user, $message))->toBeTrue();
});

test('chat message policy denies non-owner view', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $message = ChatMessage::factory()->for($owner)->create();

    expect((new ChatMessagePolicy)->view($other, $message))->toBeFalse();
});

test('chat message policy allows owner to delete', function () {
    $user = User::factory()->create();
    $message = ChatMessage::factory()->for($user)->create();

    expect((new ChatMessagePolicy)->delete($user, $message))->toBeTrue();
});

test('chat message policy denies non-owner delete', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $message = ChatMessage::factory()->for($owner)->create();

    expect((new ChatMessagePolicy)->delete($other, $message))->toBeFalse();
});

// UsageTrackingPolicy

test('usage tracking policy allows owner to view', function () {
    $user = User::factory()->create();
    $tracking = UsageTracking::factory()->for($user)->create();

    expect((new UsageTrackingPolicy)->view($user, $tracking))->toBeTrue();
});

test('usage tracking policy denies non-owner view', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $tracking = UsageTracking::factory()->for($owner)->create();

    expect((new UsageTrackingPolicy)->view($other, $tracking))->toBeFalse();
});
