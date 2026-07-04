<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * VU phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class VUPhone extends BasePhone
{
    /**
     * Wrap a raw VU phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'VU');
    }

    /**
     * Create an VUPhone from a raw number.
     */
    public static function make(string $number): VUPhone
    {
        return new self($number);
    }
}
