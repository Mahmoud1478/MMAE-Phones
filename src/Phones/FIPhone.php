<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * FI phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class FIPhone extends BasePhone
{
    /**
     * Wrap a raw FI phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'FI');
    }

    /**
     * Create an FIPhone from a raw number.
     */
    public static function make(string $number): FIPhone
    {
        return new self($number);
    }
}
