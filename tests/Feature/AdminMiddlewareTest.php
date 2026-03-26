<?php

use App\Http\Middleware\IsAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('admin middleware allows admin users', function () {
    $admin = User::factory()->admin()->create();

    $request = Request::create('/admin-test', 'GET');
    $request->setUserResolver(fn () => $admin);

    $response = (new IsAdmin)->handle($request, fn () => new Response('ok'));

    expect($response->getContent())->toBe('ok');
});

test('admin middleware blocks non-admin users', function () {
    $user = User::factory()->create();

    $request = Request::create('/admin-test', 'GET');
    $request->setUserResolver(fn () => $user);

    (new IsAdmin)->handle($request, fn () => new Response('ok'));
})->throws(\Symfony\Component\HttpKernel\Exception\HttpException::class);

test('admin middleware blocks guests', function () {
    $request = Request::create('/admin-test', 'GET');
    $request->setUserResolver(fn () => null);

    (new IsAdmin)->handle($request, fn () => new Response('ok'));
})->throws(\Symfony\Component\HttpKernel\Exception\HttpException::class);

test('is_admin defaults to false for new users', function () {
    $user = User::factory()->create();

    expect($user->is_admin)->toBeFalse();
});

test('admin factory state sets is_admin to true', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->is_admin)->toBeTrue();
});
