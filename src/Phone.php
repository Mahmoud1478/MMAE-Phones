<?php

namespace MMAE\Phones;

use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\EGPhone;

/**
 * Generic phone number taking an explicit country code.
 *
 * Use when the country varies at runtime (e.g. multi-country registration):
 * `Phone::make($number, $user->country_code)`. Concrete country classes
 * ({@see EGPhone}, ...) lock their code and take only the
 * number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, ...).
 */
final class Phone extends BasePhone
{
    /**
     * create new object
     */
    public static function make(string $number, string $countryCode): self
    {
        return new Phone($number, $countryCode);
    }
}
