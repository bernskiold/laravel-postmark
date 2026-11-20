# Changelog

This file records all the important changes to `laravel-postmark`.

## 1.0.1 - 2026-11-20

### What's Changed

* Add PHP 8.5 and 8.6 support and drop Laravel 11 by @ErikBernskiold in https://github.com/bernskiold/laravel-postmark/pull/3
* Make PHP 8.6 a required part of the test matrix by @ErikBernskiold in https://github.com/bernskiold/laravel-postmark/pull/4

### New Contributors

* @ErikBernskiold made their first contribution in https://github.com/bernskiold/laravel-postmark/pull/3

**Full Changelog**: https://github.com/bernskiold/laravel-postmark/compare/1.0.0...1.0.1

## 1.0.0 - 2026-10-25

First release.

- The `Route::postmarkWebhooks()` macro adds an authenticated webhook endpoint.
- The `VerifyPostmarkWebhook` middleware checks the HTTP Basic Auth credentials of the webhook.
- The `ProcessPostmarkWebhook` job changes each webhook into an event. You can set its connection and queue.
- The `EmailBounced`, `EmailDelivered`, `EmailOpened`, `EmailClicked`, `EmailComplained` and `EmailSubscriptionChanged` events have typed methods for the payload, metadata, client and location.
- The `RecordType` enum connects each Postmark record type to its event.
- The `InactiveRecipients` helper identifies a Postmark 406 "inactive recipient" rejection and finds the addresses in it.

Supports PHP 8.2–8.4 and Laravel 11–13.
