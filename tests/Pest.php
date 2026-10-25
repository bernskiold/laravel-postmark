<?php

use Bernskiold\LaravelPostmark\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/**
 * The example payload for a webhook, from the Postmark documentation.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function postmarkPayload(string $fixture, array $overrides = []): array
{
    $payload = json_decode(file_get_contents(__DIR__."/Fixtures/webhooks/{$fixture}.json"), true);

    return array_replace($payload, $overrides);
}
