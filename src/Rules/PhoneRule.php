<?php

declare(strict_types=1);

namespace MMAE\Phones\Rules;

use MMAE\Phones\Base\BasePhoneRule;

/**
 * generic rule taking an explicit country code, mirroring MMAE\Phones\Phone.
 *
 * use when the country varies per request (e.g. multi-country registration):
 * `PhoneRule::make($user->country_code)`. concrete country rules
 * (EGPhoneRule, SAPhoneRule, ...) lock their locale and take no code.
 */
final class PhoneRule extends BasePhoneRule
{
    public function __construct(string $countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * create a rule for the given country code
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
