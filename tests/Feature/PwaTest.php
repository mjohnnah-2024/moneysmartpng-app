<?php

it('serves the web manifest', function () {
    $response = $this->get('/manifest.webmanifest');

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/manifest+json');
});

it('manifest contains required PWA fields', function () {
    $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true);

    expect($manifest)
        ->toHaveKey('name', 'MoneySmart PNG')
        ->toHaveKey('short_name', 'MoneySmart')
        ->toHaveKey('start_url', '/')
        ->toHaveKey('display', 'standalone')
        ->toHaveKey('theme_color', '#1B4332')
        ->toHaveKey('background_color', '#FAFAF7')
        ->toHaveKey('icons');

    expect($manifest['icons'])->toBeArray()->not->toBeEmpty();
});

it('manifest icons reference existing files', function () {
    $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true);

    foreach ($manifest['icons'] as $icon) {
        $iconPath = public_path(ltrim($icon['src'], '/'));
        expect(file_exists($iconPath))->toBeTrue("Icon file missing: {$icon['src']}");
    }
});

it('service worker file exists', function () {
    expect(file_exists(public_path('sw.js')))->toBeTrue();

    $content = file_get_contents(public_path('sw.js'));
    expect($content)
        ->toContain('install')
        ->toContain('activate')
        ->toContain('fetch');
});

it('offline page exists', function () {
    expect(file_exists(public_path('offline.html')))->toBeTrue();

    $content = file_get_contents(public_path('offline.html'));
    expect($content)->toContain("You're offline");
});

it('includes manifest link in the main page', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee('manifest.webmanifest', false);
    $response->assertSee('theme-color', false);
});

it('includes apple-mobile-web-app meta tags', function () {
    $response = $this->get('/');

    $response->assertSee('apple-mobile-web-app-capable', false);
    $response->assertSee('apple-mobile-web-app-status-bar-style', false);
});
