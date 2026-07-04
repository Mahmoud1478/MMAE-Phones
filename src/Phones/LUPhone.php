<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * LU phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class LUPhone extends BasePhone
{
    /**
     * Wrap a raw LU phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'LU');
    }

    /**
     * Create an LUPhone from a raw number.
     */
    public static function make(string $number): LUPhone
    {
        return new self($number);
    }
}
