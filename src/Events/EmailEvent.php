<?php

namespace Bernskiold\LaravelPostmark\Events;

use Bernskiold\LaravelPostmark\Enums\RecordType;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * A Postmark outbound webhook, with typed methods for its payload.
 */
abstract class EmailEvent
{
    use Dispatchable;

    /**
     * @param  array<string, mixed>  $payload  The full webhook body from Postmark.
     */
    public function __construct(
        public array $payload,
    ) {}

    /**
     * The payload key that contains the time of the event.
     */
    abstract protected function timestampKey(): string;

    /**
     * Gets a value from the payload. Use "dot" notation for nested keys.
     */
    public function payloadValue(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->payload, $key, $default);
    }

    public function recordType(): ?RecordType
    {
        return RecordType::tryFrom((string) $this->payloadValue('RecordType', ''));
    }

    public function messageId(): string
    {
        return (string) $this->payloadValue('MessageID', '');
    }

    /**
     * The address of the recipient.
     *
     * Delivery, open, click and subscription records put the address in
     * `Recipient`. Bounce and spam complaint records put it in `Email`.
     */
    public function recipient(): string
    {
        return (string) ($this->payloadValue('Recipient') ?? $this->payloadValue('Email', ''));
    }

    public function serverId(): ?int
    {
        $serverId = $this->payloadValue('ServerID');

        return $serverId === null ? null : (int) $serverId;
    }

    public function messageStream(): string
    {
        return (string) $this->payloadValue('MessageStream', 'outbound');
    }

    public function tag(): ?string
    {
        return $this->payloadValue('Tag') ?: null;
    }

    /**
     * The metadata that the sender attached to the message.
     *
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return (array) $this->payloadValue('Metadata', []);
    }

    public function metadataValue(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->metadata(), $key, $default);
    }

    public function hasMetadata(string $key): bool
    {
        return array_key_exists($key, $this->metadata());
    }

    /**
     * The time of the event. If the payload has no time, this is the current time.
     */
    public function timestamp(): Carbon
    {
        $timestamp = $this->payloadValue($this->timestampKey());

        return $timestamp ? Carbon::parse($timestamp) : Carbon::now();
    }

    public function from(): ?string
    {
        return $this->payloadValue('From');
    }

    public function subject(): ?string
    {
        return $this->payloadValue('Subject');
    }
}
