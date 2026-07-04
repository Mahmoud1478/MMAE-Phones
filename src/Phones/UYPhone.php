<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * UY phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class UYPhone extends BasePhone
{
    /**
     * Wrap a raw UY phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'UY');
    }

    /**
     * Create an UYPhone from a raw number.
     */
    public static function make(string $number): UYPhone
    {
        return new self($number);
    }
}
