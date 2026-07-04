<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * FR phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class FRPhone extends BasePhone
{
    /**
     * Wrap a raw FR phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'FR');
    }

    /**
     * Create an FRPhone from a raw number.
     */
    public static function make(string $number): FRPhone
    {
        return new self($number);
    }
}
