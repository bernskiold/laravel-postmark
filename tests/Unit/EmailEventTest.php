<?php

use Bernskiold\LaravelPostmark\Enums\RecordType;
use Bernskiold\LaravelPostmark\Events\EmailBounced;
use Bernskiold\LaravelPostmark\Events\EmailClicked;
use Bernskiold\LaravelPostmark\Events\EmailComplained;
use Bernskiold\LaravelPostmark\Events\EmailDelivered;
use Bernskiold\LaravelPostmark\Events\EmailOpened;
use Bernskiold\LaravelPostmark\Events\EmailSubscriptionChanged;
use Illuminate\Support\Carbon;

it('reads the fields every record shares', function () {
    $event = new EmailDelivered(postmarkPayload('delivery'));

    expect($event->recordType())->toBe(RecordType::Delivery)
        ->and($event->messageId())->toBe('883953f4-6105-42a2-a16a-77a8eac79483')
        ->and($event->recipient())->toBe('john@example.com')
        ->and($event->serverId())->toBe(23)
        ->and($event->messageStream())->toBe('outbound')
        ->and($event->tag())->toBe('welcome-email')
        ->and($event->details())->toBe('Test delivery webhook details');
});

it('reads the recipient from Email on records that do not send Recipient', function (string $fixture, string $eventClass) {
    $event = new $eventClass(postmarkPayload($fixture, ['Email' => 'bounced@example.com']));

    expect($event->recipient())->toBe('bounced@example.com');
})->with([
    'bounce' => ['bounce', EmailBounced::class],
    'spam complaint' => ['complaint', EmailComplained::class],
]);

it('reads the metadata the message was sent with', function () {
    $event = new EmailBounced(postmarkPayload('bounce'));

    expect($event->metadata())->toBe(['a_key' => 'a_value', 'b_key' => 'b_value'])
        ->and($event->metadataValue('a_key'))->toBe('a_value')
        ->and($event->metadataValue('missing', 'fallback'))->toBe('fallback')
        ->and($event->hasMetadata('b_key'))->toBeTrue()
        ->and($event->hasMetadata('missing'))->toBeFalse();
});

it('treats absent optional fields as empty', function () {
    $event = new EmailDelivered(['RecordType' => 'Delivery']);

    expect($event->messageId())->toBe('')
        ->and($event->recipient())->toBe('')
        ->and($event->serverId())->toBeNull()
        ->and($event->messageStream())->toBe('outbound')
        ->and($event->tag())->toBeNull()
        ->and($event->metadata())->toBe([])
        ->and($event->from())->toBeNull()
        ->and($event->subject())->toBeNull();
});

it('takes the timestamp from the field the record type uses', function (string $fixture, string $eventClass, string $expected) {
    $event = new $eventClass(postmarkPayload($fixture));

    expect($event->timestamp()->toIso8601ZuluString())->toBe($expected);
})->with([
    'bounce' => ['bounce', EmailBounced::class, '2019-11-05T16:33:54Z'],
    'click' => ['click', EmailClicked::class, '2017-10-25T15:21:11Z'],
    'delivery' => ['delivery', EmailDelivered::class, '2019-11-05T16:33:54Z'],
    'open' => ['open', EmailOpened::class, '2019-11-05T16:33:54Z'],
    'spam complaint' => ['complaint', EmailComplained::class, '2019-11-05T16:33:54Z'],
    'subscription change' => ['subscription-change', EmailSubscriptionChanged::class, '2020-02-01T10:53:34Z'],
]);

it('falls back to now when the record carries no timestamp', function () {
    Carbon::setTestNow('2026-10-25 12:00:00');

    $event = new EmailDelivered(postmarkPayload('delivery', ['DeliveredAt' => null]));

    expect($event->timestamp()->equalTo(Carbon::now()))->toBeTrue();
});

it('describes a bounce', function () {
    $event = new EmailBounced(postmarkPayload('bounce'));

    expect($event->id())->toBe(4323372036854775807)
        ->and($event->type())->toBe('HardBounce')
        ->and($event->typeCode())->toBe(1)
        ->and($event->name())->toBe('Hard bounce')
        ->and($event->description())->toStartWith('The server was unable to deliver')
        ->and($event->details())->toBe('Test bounce details')
        ->and($event->isHardBounce())->toBeTrue()
        ->and($event->isSoftBounce())->toBeFalse()
        ->and($event->isInactive())->toBeTrue()
        ->and($event->canActivate())->toBeTrue()
        ->and($event->isDumpAvailable())->toBeTrue()
        ->and($event->content())->toBe('<Full dump of bounce>')
        ->and($event->from())->toBe('sender@example.com')
        ->and($event->subject())->toBe('Test subject');
});

it('recognises a soft bounce', function () {
    $event = new EmailBounced(postmarkPayload('bounce', ['Type' => 'SoftBounce']));

    expect($event->isSoftBounce())->toBeTrue()
        ->and($event->isHardBounce())->toBeFalse();
});

it('describes a spam complaint', function () {
    $event = new EmailComplained(postmarkPayload('complaint'));

    expect($event->id())->toBe(42)
        ->and($event->type())->toBe('SpamComplaint')
        ->and($event->typeCode())->toBe(512)
        ->and($event->isInactive())->toBeTrue()
        ->and($event->canActivate())->toBeFalse()
        ->and($event->content())->toBe('<Abuse report dump>');
});

it('describes an open, its client and where it came from', function () {
    $event = new EmailOpened(postmarkPayload('open', ['ReadSeconds' => 5]));

    expect($event->isFirstOpen())->toBeTrue()
        ->and($event->readSeconds())->toBe(5)
        ->and((new EmailOpened(postmarkPayload('open')))->readSeconds())->toBeNull()
        ->and($event->platform())->toBe('WebMail')
        ->and($event->deviceType())->toBe('webmail')
        ->and($event->userAgent())->toStartWith('Mozilla/5.0')
        ->and($event->clientName())->toBe('Chrome 35.0.1916.153')
        ->and($event->clientFamily())->toBe('Chrome')
        ->and($event->clientCompany())->toBe('Google')
        ->and($event->clientInfo())->toHaveKeys(['Name', 'Family', 'Company'])
        ->and($event->osName())->toBe('OS X 10.7 Lion')
        ->and($event->osFamily())->toBe('OS X 10')
        ->and($event->osCompany())->toBe('Apple Computer, Inc.')
        ->and($event->osInfo())->toHaveKeys(['Name', 'Family', 'Company'])
        ->and($event->ipAddress())->toBe('188.2.95.4')
        ->and($event->city())->toBe('Novi Sad')
        ->and($event->country())->toBe('Serbia')
        ->and($event->countryISOCode())->toBe('RS')
        ->and($event->region())->toBe('Autonomna Pokrajina Vojvodina')
        ->and($event->regionISOCode())->toBe('VO')
        ->and($event->zip())->toBe('21000')
        ->and($event->coordinates())->toBe('45.2517,19.8369')
        ->and($event->latLng())->toBe(['lat' => 45.2517, 'lng' => 19.8369])
        ->and($event->geoInfo())->toHaveKey('IP');
});

it('has no device or position when Postmark could not tell', function () {
    $event = new EmailOpened(postmarkPayload('open', ['Platform' => null, 'Geo' => ['Coords' => 'nowhere']]));

    expect($event->deviceType())->toBeNull()
        ->and($event->latLng())->toBeNull()
        ->and((new EmailOpened(postmarkPayload('open', ['Geo' => null])))->latLng())->toBeNull();
});

it('describes a click', function () {
    $event = new EmailClicked(postmarkPayload('click'));

    expect($event->originalLink())->toBe('https://example.com')
        ->and($event->clickLocation())->toBe('HTML')
        ->and($event->isHtmlClick())->toBeTrue()
        ->and($event->isTextClick())->toBeFalse()
        ->and($event->deviceType())->toBe('desktop')
        ->and($event->ipAddress())->toBe('8.8.8.8');
});

it('describes a subscription change', function () {
    $event = new EmailSubscriptionChanged(postmarkPayload('subscription-change'));

    expect($event->recipient())->toBe('bounced-address@wildbit.com')
        ->and($event->origin())->toBe('Recipient')
        ->and($event->isRecipientInitiated())->toBeTrue()
        ->and($event->isCustomerInitiated())->toBeFalse()
        ->and($event->isAdminInitiated())->toBeFalse()
        ->and($event->shouldSuppressSending())->toBeTrue()
        ->and($event->suppressionReason())->toBe('HardBounce')
        ->and($event->isHardBounceSuppression())->toBeTrue()
        ->and($event->isSpamComplaintSuppression())->toBeFalse()
        ->and($event->isManualSuppression())->toBeFalse();
});

it('recognises each suppression reason and origin', function (array $overrides, string $check) {
    $event = new EmailSubscriptionChanged(postmarkPayload('subscription-change', $overrides));

    expect($event->{$check}())->toBeTrue();
})->with([
    'spam complaint' => [['SuppressionReason' => 'SpamComplaint'], 'isSpamComplaintSuppression'],
    'manual suppression' => [['SuppressionReason' => 'ManualSuppression'], 'isManualSuppression'],
    'customer' => [['Origin' => 'Customer'], 'isCustomerInitiated'],
    'admin' => [['Origin' => 'Admin'], 'isAdminInitiated'],
]);

it('describes a reactivation', function () {
    $event = new EmailSubscriptionChanged(postmarkPayload('subscription-change', [
        'SuppressSending' => false,
        'SuppressionReason' => null,
    ]));

    expect($event->shouldSuppressSending())->toBeFalse()
        ->and($event->suppressionReason())->toBeNull();
});
