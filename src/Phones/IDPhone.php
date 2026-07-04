<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * ID phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class IDPhone extends BasePhone
{
    /**
     * Wrap a raw ID phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'ID');
    }

    /**
     * Create an IDPhone from a raw number.
     */
    public static function make(string $number): IDPhone
    {
        return new self($number);
    }
}
