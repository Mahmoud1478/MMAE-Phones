<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phone;

/**
 * generic placeholder taking an explicit country code, mirroring
 * {@see Phone}.
 *
 * use when the country varies at runtime:
 * `Placeholder::make($user->country_code)->extract()`. concrete country
 * placeholders (EGPlaceholder, ...) lock their code and take none.
 */
final class Placeholder extends BasePlaceholder
{
    /**
     * create a placeholder for the given country code
     *
     * @throws \InvalidArgumentException when no country code is given
     */
    public static function make(string $countryCode, string $mask = 'X'): static
    {
        if ($countryCode === '') {
            throw new \InvalidArgumentException('Placeholder requires a country code.');
        }

        return new self($countryCode, $mask);
    }
}
