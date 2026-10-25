<?php

namespace Bernskiold\LaravelPostmark\Enums;

use Bernskiold\LaravelPostmark\Events\EmailBounced;
use Bernskiold\LaravelPostmark\Events\EmailClicked;
use Bernskiold\LaravelPostmark\Events\EmailComplained;
use Bernskiold\LaravelPostmark\Events\EmailDelivered;
use Bernskiold\LaravelPostmark\Events\EmailEvent;
use Bernskiold\LaravelPostmark\Events\EmailOpened;
use Bernskiold\LaravelPostmark\Events\EmailSubscriptionChanged;

/**
 * The `RecordType` that Postmark sends with each outbound webhook.
 */
enum RecordType: string
{
    case Bounce = 'Bounce';
    case Click = 'Click';
    case Delivery = 'Delivery';
    case Open = 'Open';
    case SpamComplaint = 'SpamComplaint';
    case SubscriptionChange = 'SubscriptionChange';

    /**
     * The event that the package dispatches for this record type.
     *
     * @return class-string<EmailEvent>
     */
    public function eventClass(): string
    {
        return match ($this) {
            self::Bounce => EmailBounced::class,
            self::Click => EmailClicked::class,
            self::Delivery => EmailDelivered::class,
            self::Open => EmailOpened::class,
            self::SpamComplaint => EmailComplained::class,
            self::SubscriptionChange => EmailSubscriptionChanged::class,
        };
    }
}
