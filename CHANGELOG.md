# Changelog

This file records all the important changes to `laravel-postmark`.

## 1.0.0 - 2026-10-25

First release.

- The `Route::postmarkWebhooks()` macro adds an authenticated webhook endpoint.
- The `VerifyPostmarkWebhook` middleware checks the HTTP Basic Auth credentials of the webhook.
- The `ProcessPostmarkWebhook` job changes each webhook into an event. You can set its connection and queue.
- The `EmailBounced`, `EmailDelivered`, `EmailOpened`, `EmailClicked`, `EmailComplained` and `EmailSubscriptionChanged` events have typed methods for the payload, metadata, client and location.
- The `RecordType` enum connects each Postmark record type to its event.
- The `InactiveRecipients` helper identifies a Postmark 406 "inactive recipient" rejection and finds the addresses in it.

Supports PHP 8.2–8.4 and Laravel 11–13.
