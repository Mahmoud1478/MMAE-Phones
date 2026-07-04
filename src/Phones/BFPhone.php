<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BF phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BFPhone extends BasePhone
{
    /**
     * Wrap a raw BF phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BF');
    }

    /**
     * Create an BFPhone from a raw number.
     */
    public static function make(string $number): BFPhone
    {
        return new self($number);
    }
}
