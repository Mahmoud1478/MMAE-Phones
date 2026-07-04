<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MA phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MAPhone extends BasePhone
{
    /**
     * Wrap a raw MA phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MA');
    }

    /**
     * Create an MAPhone from a raw number.
     */
    public static function make(string $number): MAPhone
    {
        return new self($number);
    }
}
