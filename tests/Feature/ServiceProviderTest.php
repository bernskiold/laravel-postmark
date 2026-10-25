<?php

use Bernskiold\LaravelPostmark\Http\Controllers\WebhookController;
use Bernskiold\LaravelPostmark\Http\Middleware\VerifyPostmarkWebhook;
use Illuminate\Support\Facades\Route;

it('merges the default config', function () {
    expect(config('postmark.webhooks'))->toHaveKeys(['username', 'password', 'connection', 'queue'])
        ->and(config()->has('postmark.log_channel'))->toBeTrue();
});

it('registers the webhook route where the application asks for it', function () {
    $route = Route::postmarkWebhooks('incoming/postmark')->name('postmark.webhooks');

    expect($route->uri())->toBe('incoming/postmark')
        ->and($route->methods())->toContain('POST')
        ->and($route->getActionName())->toBe(WebhookController::class)
        ->and($route->gatherMiddleware())->toContain(VerifyPostmarkWebhook::class);
});
