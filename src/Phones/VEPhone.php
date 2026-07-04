<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * VE phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class VEPhone extends BasePhone
{
    /**
     * Wrap a raw VE phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'VE');
    }

    /**
     * Create an VEPhone from a raw number.
     */
    public static function make(string $number): VEPhone
    {
        return new self($number);
    }
}
