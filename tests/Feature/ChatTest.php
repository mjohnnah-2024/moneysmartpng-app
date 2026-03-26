<?php

use App\Ai\Agents\BudgetCoach;
use App\Models\ChatMessage;
use App\Models\Profile;
use App\Models\UsageTracking;
use App\Models\User;
use Laravel\Ai\Prompts\AgentPrompt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Profile::factory()->for($this->user)->create();
    $this->actingAs($this->user);

    BudgetCoach::fake(['Great advice about saving money in PNG!']);
});

test('guest cannot access chat', function () {
    auth()->logout();
    $this->get(route('chat.index'))->assertRedirect(route('login'));
});

test('user without profile is redirected to onboarding', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $this->get(route('chat.index'))->assertRedirect(route('onboarding.show'));
});

test('can view chat page', function () {
    $this->get(route('chat.index'))->assertSuccessful();
});

test('chat page shows existing messages', function () {
    ChatMessage::factory()->for($this->user)->fromUser()->create(['content' => 'Hello coach']);
    ChatMessage::factory()->for($this->user)->fromAssistant()->create(['content' => 'Hi there!']);

    $response = $this->get(route('chat.index'));
    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('chat')
        ->has('messages', 2)
        ->has('usage')
    );
});

test('validates message is required', function () {
    $this->postJson(route('chat.store'), [])->assertUnprocessable()->assertJsonValidationErrors('message');
});

test('sanitizes message exceeding max length by truncating', function () {
    $this->postJson(route('chat.store'), [
        'message' => str_repeat('a', 2001),
    ])->assertSuccessful();
});

test('can send message and receive AI response', function () {
    $response = $this->postJson(route('chat.store'), [
        'message' => 'How can I save more money?',
    ]);

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'userMessage' => ['id', 'role', 'content', 'created_at'],
        'assistantMessage' => ['id', 'role', 'content', 'created_at'],
    ]);
    $response->assertJsonPath('userMessage.content', 'How can I save more money?');
    $response->assertJsonPath('assistantMessage.role', 'assistant');

    expect(ChatMessage::where('user_id', $this->user->id)->count())->toBe(2);

    BudgetCoach::assertPrompted(fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'How can I save more money?'));
});

test('AI prompt includes financial context in instructions', function () {
    $this->user->profile->update(['monthly_income' => 3000]);

    $this->postJson(route('chat.store'), [
        'message' => 'What is my budget?',
    ]);

    BudgetCoach::assertPrompted(fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'What is my budget?'));
});

test('can clear chat history', function () {
    ChatMessage::factory(5)->for($this->user)->create();

    $response = $this->deleteJson(route('chat.clear'));
    $response->assertSuccessful();

    expect(ChatMessage::where('user_id', $this->user->id)->count())->toBe(0);
});

test('free plan user is limited to 20 messages', function () {
    UsageTracking::create([
        'user_id' => $this->user->id,
        'feature' => 'ai_chat',
        'period' => now()->format('Y-m'),
        'count' => 20,
    ]);

    $response = $this->postJson(route('chat.store'), [
        'message' => 'This should be blocked',
    ]);

    $response->assertStatus(429);
    $response->assertJson(['error' => 'You have reached your free plan limit of 20 AI messages. Upgrade to premium for unlimited access.']);
});

test('usage tracking increments on message send', function () {
    $this->postJson(route('chat.store'), [
        'message' => 'Track this',
    ]);

    $tracking = UsageTracking::where('user_id', $this->user->id)
        ->where('feature', 'ai_chat')
        ->first();

    expect($tracking)->not->toBeNull();
    expect($tracking->count)->toBe(1);
});

test('clear only deletes own messages', function () {
    $otherUser = User::factory()->create();
    ChatMessage::factory(3)->for($this->user)->create();
    ChatMessage::factory(2)->for($otherUser)->create();

    $this->deleteJson(route('chat.clear'));

    expect(ChatMessage::where('user_id', $this->user->id)->count())->toBe(0);
    expect(ChatMessage::where('user_id', $otherUser->id)->count())->toBe(2);
});
