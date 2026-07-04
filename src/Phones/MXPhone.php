<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MX phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MXPhone extends BasePhone
{
    /**
     * Wrap a raw MX phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MX');
    }

    /**
     * Create an MXPhone from a raw number.
     */
    public static function make(string $number): MXPhone
    {
        return new self($number);
    }
}
