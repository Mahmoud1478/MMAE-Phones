<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BI phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BIPhone extends BasePhone
{
    /**
     * Wrap a raw BI phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BI');
    }

    /**
     * Create an BIPhone from a raw number.
     */
    public static function make(string $number): BIPhone
    {
        return new self($number);
    }
}
