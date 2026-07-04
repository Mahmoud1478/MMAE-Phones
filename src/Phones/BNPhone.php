<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BN phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BNPhone extends BasePhone
{
    /**
     * Wrap a raw BN phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BN');
    }

    /**
     * Create an BNPhone from a raw number.
     */
    public static function make(string $number): BNPhone
    {
        return new self($number);
    }
}
