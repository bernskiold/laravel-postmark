<?php

use Bernskiold\LaravelPostmark\Events\EmailBounced;
use Bernskiold\LaravelPostmark\Events\EmailClicked;
use Bernskiold\LaravelPostmark\Events\EmailComplained;
use Bernskiold\LaravelPostmark\Events\EmailDelivered;
use Bernskiold\LaravelPostmark\Events\EmailOpened;
use Bernskiold\LaravelPostmark\Events\EmailSubscriptionChanged;
use Bernskiold\LaravelPostmark\Jobs\ProcessPostmarkWebhook;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
});

it('dispatches the event for the record type', function (string $fixture, string $eventClass) {
    $payload = postmarkPayload($fixture);

    (new ProcessPostmarkWebhook($payload))->handle();

    Event::assertDispatched($eventClass, fn ($event) => $event->payload === $payload);
    Event::assertDispatchedTimes($eventClass, 1);
})->with([
    'bounce' => ['bounce', EmailBounced::class],
    'click' => ['click', EmailClicked::class],
    'delivery' => ['delivery', EmailDelivered::class],
    'open' => ['open', EmailOpened::class],
    'spam complaint' => ['complaint', EmailComplained::class],
    'subscription change' => ['subscription-change', EmailSubscriptionChanged::class],
]);

it('fails without dispatching when the record type is missing', function () {
    $job = (new ProcessPostmarkWebhook(['MessageID' => 'abc']))->withFakeQueueInteractions();

    $job->handle();

    $job->assertFailed();
    Event::assertNothingDispatched();
});

it('fails without dispatching when the record type is not one Postmark sends', function () {
    $job = (new ProcessPostmarkWebhook(postmarkPayload('delivery', ['RecordType' => 'Inbound'])))->withFakeQueueInteractions();

    $job->handle();

    $job->assertFailed();
    Event::assertNothingDispatched();
});

it('uses the default connection and queue when none is configured', function () {
    $job = new ProcessPostmarkWebhook(postmarkPayload('delivery'));

    expect($job->connection)->toBeNull()
        ->and($job->queue)->toBeNull();
});
