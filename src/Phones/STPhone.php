<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * ST phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class STPhone extends BasePhone
{
    /**
     * Wrap a raw ST phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'ST');
    }

    /**
     * Create an STPhone from a raw number.
     */
    public static function make(string $number): STPhone
    {
        return new self($number);
    }
}
