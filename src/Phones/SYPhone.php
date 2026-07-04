<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SY phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SYPhone extends BasePhone
{
    /**
     * Wrap a raw SY phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SY');
    }

    /**
     * Create an SYPhone from a raw number.
     */
    public static function make(string $number): SYPhone
    {
        return new self($number);
    }
}
