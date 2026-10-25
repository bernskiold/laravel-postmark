<?php

namespace Bernskiold\LaravelPostmark\Http\Controllers;

use Bernskiold\LaravelPostmark\Jobs\ProcessPostmarkWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Replies to Postmark immediately and puts the webhook on the queue. Thus a
 * slow listener cannot cause a Postmark timeout and a second delivery.
 */
class WebhookController
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::channel(config('postmark.log_channel'))->info('Received Postmark webhook', [
            'record_type' => $payload['RecordType'] ?? 'unknown',
            'message_id' => $payload['MessageID'] ?? 'unknown',
        ]);

        dispatch(new ProcessPostmarkWebhook($payload));

        return new JsonResponse(['status' => 'ok']);
    }
}
