<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BD phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BDPhone extends BasePhone
{
    /**
     * Wrap a raw BD phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BD');
    }

    /**
     * Create an BDPhone from a raw number.
     */
    public static function make(string $number): BDPhone
    {
        return new self($number);
    }
}
