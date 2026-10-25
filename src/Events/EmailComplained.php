<?php

namespace Bernskiold\LaravelPostmark\Events;

use Bernskiold\LaravelPostmark\Concerns\HasBounceDetails;

class EmailComplained extends EmailEvent
{
    use HasBounceDetails;

    /**
     * Postmark also uses `BouncedAt` for the time of a spam complaint.
     */
    protected function timestampKey(): string
    {
        return 'BouncedAt';
    }
}
