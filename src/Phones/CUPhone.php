<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * CU phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class CUPhone extends BasePhone
{
    /**
     * Wrap a raw CU phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'CU');
    }

    /**
     * Create an CUPhone from a raw number.
     */
    public static function make(string $number): CUPhone
    {
        return new self($number);
    }
}
