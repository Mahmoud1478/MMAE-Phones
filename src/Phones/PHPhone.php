<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * PH phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class PHPhone extends BasePhone
{
    /**
     * Wrap a raw PH phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'PH');
    }

    /**
     * Create an PHPhone from a raw number.
     */
    public static function make(string $number): PHPhone
    {
        return new self($number);
    }
}
