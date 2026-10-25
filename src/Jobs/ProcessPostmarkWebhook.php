<?php

namespace Bernskiold\LaravelPostmark\Jobs;

use Bernskiold\LaravelPostmark\Enums\RecordType;
use Bernskiold\LaravelPostmark\Exceptions\InvalidWebhookPayload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Changes a Postmark webhook into the applicable event.
 */
class ProcessPostmarkWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 60;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public array $payload,
    ) {
        $this->onConnection(config('postmark.webhooks.connection'));
        $this->onQueue(config('postmark.webhooks.queue'));
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60, 120, 300];
    }

    public function handle(): void
    {
        $recordType = Arr::get($this->payload, 'RecordType');

        if (! $recordType) {
            $this->fail(InvalidWebhookPayload::missingRecordType($this->payload));

            return;
        }

        $type = RecordType::tryFrom($recordType);

        if (! $type) {
            $this->fail(InvalidWebhookPayload::unsupportedRecordType($recordType, $this->payload));

            return;
        }

        $eventClass = $type->eventClass();
        $event = new $eventClass($this->payload);

        Log::channel(config('postmark.log_channel'))->info("Processing Postmark {$recordType} webhook", [
            'message_id' => $event->messageId(),
            'recipient' => $event->recipient(),
            'attempt' => $this->attempts(),
        ]);

        event($event);
    }

    public function failed(Throwable $exception): void
    {
        Log::channel(config('postmark.log_channel'))->error($exception->getMessage(), [
            'record_type' => Arr::get($this->payload, 'RecordType', 'unknown'),
            'message_id' => Arr::get($this->payload, 'MessageID', 'unknown'),
            'payload' => $this->payload,
        ]);
    }
}
