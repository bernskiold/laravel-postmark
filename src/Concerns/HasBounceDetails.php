<?php

namespace Bernskiold\LaravelPostmark\Concerns;

use Bernskiold\LaravelPostmark\Events\EmailEvent;

/**
 * Postmark records a spam complaint as a type of bounce. Thus the two
 * payloads have the same fields.
 *
 * @mixin EmailEvent
 */
trait HasBounceDetails
{
    public function id(): int
    {
        return (int) $this->payloadValue('ID', 0);
    }

    /**
     * The bounce type, for example `HardBounce`, `SoftBounce` or `SpamComplaint`.
     */
    public function type(): string
    {
        return (string) $this->payloadValue('Type', '');
    }

    public function typeCode(): int
    {
        return (int) $this->payloadValue('TypeCode', 0);
    }

    /**
     * The name of the bounce type, for people to read.
     */
    public function name(): string
    {
        return (string) $this->payloadValue('Name', '');
    }

    public function description(): string
    {
        return (string) $this->payloadValue('Description', '');
    }

    public function details(): string
    {
        return (string) $this->payloadValue('Details', '');
    }

    /**
     * True if Postmark made the address inactive. Postmark refuses all sends
     * to an inactive address until you activate it again.
     */
    public function isInactive(): bool
    {
        return (bool) $this->payloadValue('Inactive', false);
    }

    public function canActivate(): bool
    {
        return (bool) $this->payloadValue('CanActivate', false);
    }

    public function isDumpAvailable(): bool
    {
        return (bool) $this->payloadValue('DumpAvailable', false);
    }

    /**
     * The full source of the bounce or complaint, if Postmark includes it.
     */
    public function content(): ?string
    {
        return $this->payloadValue('Content');
    }
}
