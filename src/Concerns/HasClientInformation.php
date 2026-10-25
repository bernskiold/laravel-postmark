<?php

namespace Bernskiold\LaravelPostmark\Concerns;

use Bernskiold\LaravelPostmark\Events\EmailEvent;

/**
 * The email client and the device of an open or a click.
 *
 * @mixin EmailEvent
 */
trait HasClientInformation
{
    /**
     * @return array{Name?: string, Company?: string, Family?: string}|null
     */
    public function clientInfo(): ?array
    {
        return $this->payloadValue('Client');
    }

    public function clientName(): ?string
    {
        return $this->payloadValue('Client.Name');
    }

    public function clientCompany(): ?string
    {
        return $this->payloadValue('Client.Company');
    }

    public function clientFamily(): ?string
    {
        return $this->payloadValue('Client.Family');
    }

    /**
     * @return array{Name?: string, Company?: string, Family?: string}|null
     */
    public function osInfo(): ?array
    {
        return $this->payloadValue('OS');
    }

    public function osName(): ?string
    {
        return $this->payloadValue('OS.Name');
    }

    public function osCompany(): ?string
    {
        return $this->payloadValue('OS.Company');
    }

    public function osFamily(): ?string
    {
        return $this->payloadValue('OS.Family');
    }

    /**
     * The platform from Postmark: `Desktop`, `Mobile`, `WebMail` or `Unknown`.
     */
    public function platform(): ?string
    {
        return $this->payloadValue('Platform');
    }

    public function userAgent(): ?string
    {
        return $this->payloadValue('UserAgent');
    }

    /**
     * The platform in lowercase.
     */
    public function deviceType(): ?string
    {
        $platform = $this->platform();

        return $platform ? strtolower($platform) : null;
    }
}
