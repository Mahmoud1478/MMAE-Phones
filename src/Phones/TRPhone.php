<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * TR phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class TRPhone extends BasePhone
{
    /**
     * Wrap a raw TR phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'TR');
    }

    /**
     * Create an TRPhone from a raw number.
     */
    public static function make(string $number): TRPhone
    {
        return new self($number);
    }
}
