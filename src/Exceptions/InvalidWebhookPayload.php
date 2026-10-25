<?php

namespace Bernskiold\LaravelPostmark\Exceptions;

use Exception;

class InvalidWebhookPayload extends Exception
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public static function missingRecordType(array $payload): self
    {
        return new self('Missing RecordType in Postmark webhook payload: '.json_encode($payload));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function unsupportedRecordType(string $recordType, array $payload): self
    {
        return new self("Unsupported Postmark webhook RecordType: {$recordType}. Payload: ".json_encode($payload));
    }
}
