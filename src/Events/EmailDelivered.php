<?php

namespace Bernskiold\LaravelPostmark\Events;

class EmailDelivered extends EmailEvent
{
    protected function timestampKey(): string
    {
        return 'DeliveredAt';
    }

    /**
     * The response from the server that received the message.
     */
    public function details(): ?string
    {
        return $this->payloadValue('Details');
    }
}
