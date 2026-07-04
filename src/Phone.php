<?php

namespace MMAE\Phones;

use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\EGPhone;

/**
 * Phone number with a runtime country code — use when the country varies
 * (e.g. multi-country registration): `Phone::make($number, $user->country_code)`.
 *
 * Concrete classes ({@see EGPhone}, ...) hardcode their code instead.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, ...).
 */
final class Phone extends BasePhone
{
    public static function make(string $number, string $countryCode): self
    {
        return new Phone($number, $countryCode);
    }
}
