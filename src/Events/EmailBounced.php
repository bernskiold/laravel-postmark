<?php

namespace Bernskiold\LaravelPostmark\Events;

use Bernskiold\LaravelPostmark\Concerns\HasBounceDetails;

class EmailBounced extends EmailEvent
{
    use HasBounceDetails;

    protected function timestampKey(): string
    {
        return 'BouncedAt';
    }

    public function isHardBounce(): bool
    {
        return $this->type() === 'HardBounce';
    }

    public function isSoftBounce(): bool
    {
        return $this->type() === 'SoftBounce';
    }
}
