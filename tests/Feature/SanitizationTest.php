<?php

use App\Models\Profile;
use App\Models\User;
use App\Support\Sanitizer;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// Sanitizer unit tests

test('sanitize text strips html tags', function () {
    expect(Sanitizer::text('<script>alert("xss")</script>Hello'))
        ->toBe('alert("xss")Hello');
});

test('sanitize text trims whitespace', function () {
    expect(Sanitizer::text('  hello world  '))
        ->toBe('hello world');
});

test('sanitize text limits length', function () {
    $long = str_repeat('a', 300);
    expect(Sanitizer::text($long, 255))
        ->toHaveLength(255);
});

test('sanitize text returns null for null input', function () {
    expect(Sanitizer::text(null))->toBeNull();
});

test('sanitize amount returns valid float', function () {
    expect(Sanitizer::amount('99.50'))->toBe(99.50);
});

test('sanitize amount caps at max', function () {
    expect(Sanitizer::amount('1500000', 999999.99))->toBe(999999.99);
});

test('sanitize amount returns null for negative', function () {
    expect(Sanitizer::amount('-10'))->toBeNull();
});

test('sanitize amount returns null for non-numeric', function () {
    expect(Sanitizer::amount('abc'))->toBeNull();
});

// Integration: HTML tags are stripped from transaction description

test('transaction description html is stripped before saving', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create();

    $this->actingAs($user)->post(route('transactions.store'), [
        'amount' => 10.00,
        'type' => 'expense',
        'category' => 'Food/Market',
        'description' => '<b>Bold</b> text <script>xss</script>',
        'date' => now()->format('Y-m-d'),
    ]);

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'description' => 'Bold text xss',
    ]);
});

test('goal name html is stripped before saving', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create();

    $this->actingAs($user)->post(route('goals.store'), [
        'name' => '<em>New Phone</em><script>alert(1)</script>',
        'target_amount' => 1000,
    ]);

    $this->assertDatabaseHas('goals', [
        'user_id' => $user->id,
        'name' => 'New Phonealert(1)',
    ]);
});
