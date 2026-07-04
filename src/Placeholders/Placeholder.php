<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phone;

/**
 * Placeholder for a runtime-supplied country code, mirroring {@see Phone}.
 *
 * `Placeholder::make($user->country_code)->extract()`. Per-country subclasses
 * (EGPlaceholder, ...) lock their code and take none.
 */
final class Placeholder extends BasePlaceholder
{
    /**
     * Create a placeholder for the given country code.
     *
     * @throws \InvalidArgumentException when the country code is empty
     */
    public static function make(string $countryCode, string $mask = 'X'): static
    {
        if ($countryCode === '') {
            throw new \InvalidArgumentException('Placeholder requires a country code.');
        }

        return new self($countryCode, $mask);
    }
}
