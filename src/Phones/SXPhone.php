<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SX phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SXPhone extends BasePhone
{
    /**
     * Wrap a raw SX phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SX');
    }

    /**
     * Create an SXPhone from a raw number.
     */
    public static function make(string $number): SXPhone
    {
        return new self($number);
    }
}
