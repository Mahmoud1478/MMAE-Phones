<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * VN phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class VNPhone extends BasePhone
{
    /**
     * Wrap a raw VN phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'VN');
    }

    /**
     * Create an VNPhone from a raw number.
     */
    public static function make(string $number): VNPhone
    {
        return new self($number);
    }
}
