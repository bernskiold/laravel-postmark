<?php

use Bernskiold\LaravelPostmark\Jobs\ProcessPostmarkWebhook;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

function postmarkCredentials(string $username = 'postmark', string $password = 'secret'): array
{
    return ['Authorization' => 'Basic '.base64_encode("{$username}:{$password}")];
}

it('accepts a webhook with valid credentials and queues it', function () {
    $payload = postmarkPayload('delivery');

    $this->postJson('webhooks/postmark', $payload, postmarkCredentials())
        ->assertOk()
        ->assertExactJson(['status' => 'ok']);

    Queue::assertPushed(ProcessPostmarkWebhook::class, fn (ProcessPostmarkWebhook $job) => $job->payload === $payload);
});

it('queues the job on the configured connection and queue', function () {
    config()->set('postmark.webhooks.connection', 'redis');
    config()->set('postmark.webhooks.queue', 'webhooks');

    $this->postJson('webhooks/postmark', postmarkPayload('open'), postmarkCredentials())->assertOk();

    Queue::assertPushed(ProcessPostmarkWebhook::class, fn (ProcessPostmarkWebhook $job) => $job->connection === 'redis' && $job->queue === 'webhooks');
});

it('refuses a webhook without credentials', function () {
    $this->postJson('webhooks/postmark', postmarkPayload('delivery'))->assertUnauthorized();

    Queue::assertNothingPushed();
});

it('refuses a webhook with the wrong credentials', function (string $username, string $password) {
    $this->postJson('webhooks/postmark', postmarkPayload('delivery'), postmarkCredentials($username, $password))
        ->assertUnauthorized();

    Queue::assertNothingPushed();
})->with([
    'wrong password' => ['postmark', 'not-the-secret'],
    'wrong username' => ['someone-else', 'secret'],
    'empty' => ['', ''],
]);

it('refuses a webhook whose Authorization header is not Basic Auth', function (string $header) {
    $this->postJson('webhooks/postmark', postmarkPayload('delivery'), ['Authorization' => $header])
        ->assertUnauthorized();

    Queue::assertNothingPushed();
})->with([
    'bearer token' => 'Bearer secret',
    'not base64' => 'Basic !!!not-base64!!!',
    'no separator' => 'Basic '.base64_encode('postmarksecret'),
]);

it('refuses every webhook until credentials are configured', function () {
    config()->set('postmark.webhooks.password', null);

    $this->postJson('webhooks/postmark', postmarkPayload('delivery'), postmarkCredentials())
        ->assertServerError();

    Queue::assertNothingPushed();
});
