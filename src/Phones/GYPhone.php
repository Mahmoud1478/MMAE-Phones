<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * GY phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class GYPhone extends BasePhone
{
    /**
     * Wrap a raw GY phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'GY');
    }

    /**
     * Create an GYPhone from a raw number.
     */
    public static function make(string $number): GYPhone
    {
        return new self($number);
    }
}
