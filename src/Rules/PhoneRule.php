<?php

declare(strict_types=1);

namespace MMAE\Phones\Rules;

use MMAE\Phones\Base\BasePhoneRule;

/**
 * Generic rule taking an explicit country code, mirroring MMAE\Phones\Phone.
 *
 * Use when the country varies per request, e.g. `PhoneRule::make($user->country_code)`.
 * Concrete country rules (EGPhoneRule, ...) lock their code and take none.
 */
final class PhoneRule extends BasePhoneRule
{
    public function __construct(string $countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * Create a rule for the given country code.
     *
     * @throws \InvalidArgumentException when no country code is given
     */
    #[\Override]
    public static function make(?string $countryCode = null): static
    {
        if ($countryCode === null) {
            throw new \InvalidArgumentException('PhoneRule requires a country code.');
        }

        return new self($countryCode);
    }
}
