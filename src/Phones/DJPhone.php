<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * DJ phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class DJPhone extends BasePhone
{
    /**
     * Wrap a raw DJ phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'DJ');
    }

    /**
     * Create an DJPhone from a raw number.
     */
    public static function make(string $number): DJPhone
    {
        return new self($number);
    }
}
