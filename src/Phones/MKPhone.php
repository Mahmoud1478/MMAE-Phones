<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MK phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MKPhone extends BasePhone
{
    /**
     * Wrap a raw MK phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MK');
    }

    /**
     * Create an MKPhone from a raw number.
     */
    public static function make(string $number): MKPhone
    {
        return new self($number);
    }
}
