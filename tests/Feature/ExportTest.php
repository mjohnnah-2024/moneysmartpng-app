<?php

use App\Models\Profile;
use App\Models\Transaction;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('free user cannot export transactions', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create(['plan' => 'free']);
    $this->actingAs($user);

    $response = $this->get(route('export.transactions'));

    $response->assertForbidden();
});

test('premium user can export transactions csv', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->premium()->create();
    $this->actingAs($user);

    Transaction::factory(3)->for($user)->create();

    $response = $this->get(route('export.transactions'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $response->assertDownload();
});

test('csv export contains correct headers', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->premium()->create();
    $this->actingAs($user);

    Transaction::factory()->for($user)->create([
        'amount' => 100.50,
        'type' => 'expense',
        'category' => 'Food/Market',
        'description' => 'Boroko market',
        'date' => '2026-01-15',
    ]);

    $response = $this->get(route('export.transactions'));

    $content = $response->streamedContent();
    expect($content)->toContain('Date,Type,Category,Amount,Description');
    expect($content)->toContain('Food/Market');
    expect($content)->toContain('Boroko market');
});
