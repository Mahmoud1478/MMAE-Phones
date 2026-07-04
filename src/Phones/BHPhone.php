<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BH phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BHPhone extends BasePhone
{
    /**
     * Wrap a raw BH phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BH');
    }

    /**
     * Create an BHPhone from a raw number.
     */
    public static function make(string $number): BHPhone
    {
        return new self($number);
    }
}
