<?php

namespace Bernskiold\LaravelPostmark\Events;

use Bernskiold\LaravelPostmark\Concerns\HasClientInformation;
use Bernskiold\LaravelPostmark\Concerns\HasGeolocation;

class EmailClicked extends EmailEvent
{
    use HasClientInformation, HasGeolocation;

    protected function timestampKey(): string
    {
        return 'ReceivedAt';
    }

    /**
     * The link in the message before Postmark changed it for click tracking.
     */
    public function originalLink(): ?string
    {
        return $this->payloadValue('OriginalLink');
    }

    /**
     * The part of the message that contains the link: `HTML` or `Text`.
     */
    public function clickLocation(): ?string
    {
        return $this->payloadValue('ClickLocation');
    }

    public function isHtmlClick(): bool
    {
        return $this->clickLocation() === 'HTML';
    }

    public function isTextClick(): bool
    {
        return $this->clickLocation() === 'Text';
    }
}
