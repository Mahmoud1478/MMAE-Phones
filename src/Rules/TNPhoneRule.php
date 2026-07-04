<?php

declare(strict_types=1);

namespace MMAE\Phones\Rules;

use MMAE\Phones\Base\BasePhoneRule;

/**
 * TN phone-number validation rule.
 *
 * @see BasePhoneRule for the fluent API (make, message, nullable, required, exists, unique, validateUsing, ...).
 */
final class TNPhoneRule extends BasePhoneRule
{
    protected string $countryCode = 'TN';
}
