# Postmark webhooks as Laravel events

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bernskiold/laravel-postmark.svg?style=flat-square)](https://packagist.org/packages/bernskiold/laravel-postmark)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/bernskiold/laravel-postmark/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/bernskiold/laravel-postmark/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/bernskiold/laravel-postmark/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/bernskiold/laravel-postmark/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/bernskiold/laravel-postmark.svg?style=flat-square)](https://packagist.org/packages/bernskiold/laravel-postmark)

Laravel sends mail through Postmark. Postmark then sends webhooks back to your application. This package receives these webhooks and changes each one into a Laravel event. You can then use a usual listener for each delivery, bounce, open, click, spam complaint and suppression.

```php
use Bernskiold\LaravelPostmark\Events\EmailBounced;

class FlagBouncedAddress
{
    public function handle(EmailBounced $event): void
    {
        if ($event->isHardBounce()) {
            Subscriber::query()
                ->where('email', $event->recipient())
                ->update(['undeliverable' => true]);
        }
    }
}
```

## What the package does

- It gives a route macro. `Route::postmarkWebhooks('webhooks/postmark')` adds the endpoint in the route group that you select.
- It checks each request against the Basic Auth credentials of the webhook in Postmark. The comparison is safe against timing attacks. If you do not set the credentials, the endpoint refuses all requests.
- It replies to Postmark immediately. A queued job then dispatches the event. The job tries again with a backoff if it fails.
- It gives typed methods for the payload, for example `isHardBounce()`, `originalLink()` and `latLng()`. Postmark puts the address in `Recipient` on some webhooks and in `Email` on other webhooks. `recipient()` reads the correct field.
- It gives the metadata that you attached to the message. Use the metadata to find the record that sent the message.
- It finds the addresses in a Postmark "inactive recipient" rejection. Postmark sends this rejection when it does not send to a suppressed address.

## Installation

Install the package with Composer:

```bash
composer require bernskiold/laravel-postmark
```

Set the credentials of the webhook in Postmark. Then set the same credentials in your environment:

```dotenv
POSTMARK_WEBHOOK_USERNAME=postmark
POSTMARK_WEBHOOK_PASSWORD=a-long-random-string
```

Add the route. Postmark sends requests from outside your application, so CSRF verification must not apply to the route. Put the route in your `api` routes, or exclude its path from CSRF verification:

```php
// routes/api.php
Route::postmarkWebhooks('webhooks/postmark');
```

The macro returns the route. You can add a name or middleware to it.

In Postmark, open the **Webhooks** settings of your server. Add a webhook with the full URL of the route. Enter the same Basic Auth credentials. Then select the events that you want to receive.

## Configuration

To change the queue or the log channel, publish the configuration file:

```bash
php artisan vendor:publish --tag=postmark-config
```

```php
return [
    'webhooks' => [
        'username' => env('POSTMARK_WEBHOOK_USERNAME'),
        'password' => env('POSTMARK_WEBHOOK_PASSWORD'),
        'connection' => env('POSTMARK_WEBHOOK_QUEUE_CONNECTION'), // null: the default connection
        'queue' => env('POSTMARK_WEBHOOK_QUEUE'),                 // null: the default queue
    ],
    'log_channel' => env('POSTMARK_LOG_CHANNEL'),                 // null: the default channel
];
```

## Events

| Postmark webhook    | Event                                                        | Time from     |
|---------------------|--------------------------------------------------------------|---------------|
| Bounce              | `Bernskiold\LaravelPostmark\Events\EmailBounced`             | `BouncedAt`   |
| Delivery            | `Bernskiold\LaravelPostmark\Events\EmailDelivered`           | `DeliveredAt` |
| Open                | `Bernskiold\LaravelPostmark\Events\EmailOpened`              | `ReceivedAt`  |
| Click               | `Bernskiold\LaravelPostmark\Events\EmailClicked`             | `ReceivedAt`  |
| Spam complaint      | `Bernskiold\LaravelPostmark\Events\EmailComplained`          | `BouncedAt`   |
| Subscription change | `Bernskiold\LaravelPostmark\Events\EmailSubscriptionChanged` | `ChangedAt`   |

Listen for these events as for all other Laravel events. All the events extend `EmailEvent` and have these methods:

```php
$event->recordType();        // RecordType enum
$event->messageId();         // the MessageID that Postmark gave when you sent the message
$event->recipient();         // the address, from Recipient or from Email
$event->timestamp();         // Carbon: the time of the event
$event->messageStream();     // for example "outbound"
$event->tag();
$event->serverId();
$event->from();
$event->subject();

$event->metadata();          // all the metadata that you attached to the message
$event->metadataValue('order_id');
$event->hasMetadata('order_id');

$event->payload;             // the full payload, for fields that have no method
$event->payloadValue('Geo.City');
```

### Bounces and spam complaints

Postmark records a spam complaint as a type of bounce. Thus `EmailBounced` and `EmailComplained` have the same methods:

```php
$event->type();              // "HardBounce", "SoftBounce", "SpamComplaint", and other types
$event->typeCode();
$event->name();              // "Hard bounce"
$event->description();
$event->details();
$event->isInactive();        // true if Postmark does not send to this address now
$event->canActivate();
$event->content();           // the full bounce or abuse report, if Postmark includes it

$event->isHardBounce();      // EmailBounced only
$event->isSoftBounce();
```

### Opens and clicks

```php
$event->isFirstOpen();       // EmailOpened
$event->readSeconds();

$event->originalLink();      // EmailClicked
$event->isHtmlClick();
$event->isTextClick();

// EmailOpened and EmailClicked
$event->platform();          // "Desktop", "Mobile", "WebMail" or "Unknown"
$event->deviceType();        // the platform in lowercase
$event->clientName();        // "Chrome 35.0.1916.153"
$event->osName();
$event->userAgent();
$event->ipAddress();
$event->country();
$event->city();
$event->latLng();            // ['lat' => 45.2517, 'lng' => 19.8369]
```

### Subscription changes

Postmark sends a subscription change when it suppresses a recipient on a message stream. It also sends one when it activates the recipient again.

```php
$event->shouldSuppressSending();     // true if suppressed, false if activated again
$event->suppressionReason();         // "HardBounce", "SpamComplaint" or "ManualSuppression"
$event->isHardBounceSuppression();
$event->isSpamComplaintSuppression();
$event->isManualSuppression();

$event->origin();                    // "Recipient", "Customer" or "Admin"
$event->isRecipientInitiated();
$event->isCustomerInitiated();
$event->isAdminInitiated();
```

## Find the message that caused an event

Attach metadata when you send the message. Postmark then includes the metadata in each event for that message.

```php
return (new MailMessage)
    ->subject('Your invoice')
    ->tag('invoice')
    ->metadata('invoice_id', $this->invoice->id);
```

```php
public function handle(EmailDelivered $event): void
{
    Invoice::find($event->metadataValue('invoice_id'))?->markAsDelivered($event->timestamp());
}
```

## Inactive recipients

Postmark makes an address inactive after a hard bounce, a spam complaint or a manual suppression. Postmark does not send to an inactive address. It returns API error 406, and the send operation throws an exception.

Only the text of the exception identifies this rejection. Postmark sets the code of the exception to zero. Laravel's notification sender also changes the class of the exception. `InactiveRecipients` examines the text for you:

```php
use Bernskiold\LaravelPostmark\Support\InactiveRecipients;

try {
    $user->notify(new InvoiceReady($invoice));
} catch (Throwable $e) {
    if (! InactiveRecipients::matches($e)) {
        throw $e;
    }

    InactiveRecipients::extract($e)->each(
        fn (string $email) => Subscriber::query()->where('email', $email)->update(['undeliverable' => true])
    );
}
```

`extract()` returns a collection of the addresses in the rejection, in lowercase.

## Failures

If the `RecordType` is missing or unknown, the job fails. The package does not ignore the webhook. It writes the full payload to the log, and the job shows in your failed jobs. This package does not process the `Inbound` webhook, because that webhook has a different payload.

## Testing

```bash
composer test
```

## Changelog

Refer to the [CHANGELOG](CHANGELOG.md) for the recent changes.

## Contributing

Refer to [CONTRIBUTING](CONTRIBUTING.md) for the procedure.

## Security Vulnerabilities

Refer to [our security policy](SECURITY.md) for how to report a security vulnerability.

## Credits

- [Erik Bernskiöld](https://bernskiold.com)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Refer to the [License File](LICENSE.md) for more information.
