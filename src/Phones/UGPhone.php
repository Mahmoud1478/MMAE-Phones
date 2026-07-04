<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * UG phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class UGPhone extends BasePhone
{
    /**
     * Wrap a raw UG phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'UG');
    }

    /**
     * Create an UGPhone from a raw number.
     */
    public static function make(string $number): UGPhone
    {
        return new self($number);
    }
}
