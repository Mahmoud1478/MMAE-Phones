<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * VG phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class VGPhone extends BasePhone
{
    /**
     * Wrap a raw VG phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'VG');
    }

    /**
     * Create an VGPhone from a raw number.
     */
    public static function make(string $number): VGPhone
    {
        return new self($number);
    }
}
