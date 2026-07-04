<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * GW phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class GWPhone extends BasePhone
{
    /**
     * Wrap a raw GW phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'GW');
    }

    /**
     * Create an GWPhone from a raw number.
     */
    public static function make(string $number): GWPhone
    {
        return new self($number);
    }
}
