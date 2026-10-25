<?php

namespace Bernskiold\LaravelPostmark\Support;

use Illuminate\Support\Collection;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

/**
 * Identifies a Postmark rejection for an inactive recipient (API error 406).
 * Finds the addresses in the rejection.
 *
 * Only the message text identifies the rejection. The Postmark transport
 * writes the Postmark error code in the message text. It sets the code of
 * the exception to zero. Laravel's NotificationSender also changes an
 * HttpTransportException into a usual TransportException. Thus the class
 * and the code of the exception are not reliable.
 */
class InactiveRecipients
{
    public static function matches(Throwable $exception): bool
    {
        return $exception instanceof TransportExceptionInterface
            && str_contains($exception->getMessage(), 'marked as inactive');
    }

    /**
     * The addresses in the rejection, in lowercase.
     *
     * The text of the rejection is: "You tried to send to recipient(s) that
     * have been marked as inactive. Found inactive addresses: a@example.com,
     * b@example.com. Inactive recipients are ones that have generated a hard
     * bounce, a spam complaint, or a manual suppression."
     *
     * @return Collection<int, string>
     */
    public static function extract(Throwable $exception): Collection
    {
        if (! preg_match('/Found inactive addresses:\s*(.+?)\.\s*Inactive recipients/i', $exception->getMessage(), $matches)) {
            return new Collection;
        }

        return Collection::make(explode(',', $matches[1]))
            ->map(fn (string $email) => strtolower(trim($email)))
            ->filter(fn (string $email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->values();
    }
}
