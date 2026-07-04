<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * CY phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class CYPhone extends BasePhone
{
    /**
     * Wrap a raw CY phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'CY');
    }

    /**
     * Create an CYPhone from a raw number.
     */
    public static function make(string $number): CYPhone
    {
        return new self($number);
    }
}
