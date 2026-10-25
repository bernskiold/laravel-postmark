<?php

use Bernskiold\LaravelPostmark\Support\InactiveRecipients;
use Symfony\Component\Mailer\Exception\TransportException;

function inactiveRecipientsRejection(string $addresses): TransportException
{
    return new TransportException(
        'Unable to send an email: You tried to send to recipient(s) that have been marked as inactive. '
        ."Found inactive addresses: {$addresses}. "
        .'Inactive recipients are ones that have generated a hard bounce, a spam complaint, or a manual suppression. (code 406).'
    );
}

it('recognises an inactive-recipient rejection', function () {
    expect(InactiveRecipients::matches(inactiveRecipientsRejection('john@example.com')))->toBeTrue();
});

it('ignores other transport failures', function () {
    expect(InactiveRecipients::matches(new TransportException('Connection could not be established with host "smtp.example.com".')))->toBeFalse();
});

it('ignores the wording when it does not come from the mail transport', function () {
    expect(InactiveRecipients::matches(new RuntimeException('Recipient marked as inactive')))->toBeFalse();
});

it('reads the refused addresses out of the rejection, lowercased', function () {
    $addresses = InactiveRecipients::extract(inactiveRecipientsRejection('John@Example.com, jane@example.com'));

    expect($addresses->all())->toBe(['john@example.com', 'jane@example.com']);
});

it('drops anything in the list that is not an address', function () {
    $addresses = InactiveRecipients::extract(inactiveRecipientsRejection('john@example.com, not-an-address'));

    expect($addresses->all())->toBe(['john@example.com']);
});

it('finds no addresses in a message that does not list them', function () {
    expect(InactiveRecipients::extract(new TransportException('Something else went wrong.'))->all())->toBe([]);
});
