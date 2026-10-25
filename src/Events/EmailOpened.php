<?php

namespace Bernskiold\LaravelPostmark\Events;

use Bernskiold\LaravelPostmark\Concerns\HasClientInformation;
use Bernskiold\LaravelPostmark\Concerns\HasGeolocation;

class EmailOpened extends EmailEvent
{
    use HasClientInformation, HasGeolocation;

    protected function timestampKey(): string
    {
        return 'ReceivedAt';
    }

    /**
     * True if this is the first time that the recipient opened the message.
     */
    public function isFirstOpen(): bool
    {
        return (bool) $this->payloadValue('FirstOpen', false);
    }

    /**
     * The time that the recipient read the message for, in seconds.
     */
    public function readSeconds(): ?int
    {
        $seconds = $this->payloadValue('ReadSeconds');

        return $seconds === null ? null : (int) $seconds;
    }
}
