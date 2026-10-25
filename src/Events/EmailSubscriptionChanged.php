<?php

namespace Bernskiold\LaravelPostmark\Events;

/**
 * Postmark suppressed a recipient on a message stream, or activated the
 * recipient again.
 */
class EmailSubscriptionChanged extends EmailEvent
{
    protected function timestampKey(): string
    {
        return 'ChangedAt';
    }

    /**
     * The origin of the change: `Recipient`, `Customer` or `Admin`.
     */
    public function origin(): ?string
    {
        return $this->payloadValue('Origin');
    }

    /**
     * True if Postmark suppressed the recipient. False if Postmark activated
     * the recipient again.
     */
    public function shouldSuppressSending(): bool
    {
        return (bool) $this->payloadValue('SuppressSending', false);
    }

    /**
     * The reason for the suppression: `HardBounce`, `SpamComplaint` or
     * `ManualSuppression`. Null if Postmark activated the recipient again.
     */
    public function suppressionReason(): ?string
    {
        return $this->payloadValue('SuppressionReason');
    }

    public function isRecipientInitiated(): bool
    {
        return $this->origin() === 'Recipient';
    }

    public function isCustomerInitiated(): bool
    {
        return $this->origin() === 'Customer';
    }

    public function isAdminInitiated(): bool
    {
        return $this->origin() === 'Admin';
    }

    public function isHardBounceSuppression(): bool
    {
        return $this->suppressionReason() === 'HardBounce';
    }

    public function isSpamComplaintSuppression(): bool
    {
        return $this->suppressionReason() === 'SpamComplaint';
    }

    public function isManualSuppression(): bool
    {
        return $this->suppressionReason() === 'ManualSuppression';
    }
}
