<?php

namespace Bernskiold\LaravelPostmark\Concerns;

use Bernskiold\LaravelPostmark\Events\EmailEvent;

/**
 * The location of an open or a click. Postmark finds the location from the
 * IP address.
 *
 * @mixin EmailEvent
 */
trait HasGeolocation
{
    /**
     * @return array<string, string>|null
     */
    public function geoInfo(): ?array
    {
        return $this->payloadValue('Geo');
    }

    public function ipAddress(): ?string
    {
        return $this->payloadValue('Geo.IP');
    }

    public function countryISOCode(): ?string
    {
        return $this->payloadValue('Geo.CountryISOCode');
    }

    public function country(): ?string
    {
        return $this->payloadValue('Geo.Country');
    }

    public function regionISOCode(): ?string
    {
        return $this->payloadValue('Geo.RegionISOCode');
    }

    public function region(): ?string
    {
        return $this->payloadValue('Geo.Region');
    }

    public function city(): ?string
    {
        return $this->payloadValue('Geo.City');
    }

    public function zip(): ?string
    {
        return $this->payloadValue('Geo.Zip');
    }

    /**
     * The coordinates in the Postmark format: `"latitude,longitude"`.
     */
    public function coordinates(): ?string
    {
        return $this->payloadValue('Geo.Coords');
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    public function latLng(): ?array
    {
        $parts = explode(',', (string) $this->coordinates());

        if (count($parts) !== 2) {
            return null;
        }

        return [
            'lat' => (float) trim($parts[0]),
            'lng' => (float) trim($parts[1]),
        ];
    }
}
