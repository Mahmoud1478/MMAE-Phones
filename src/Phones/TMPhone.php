<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * TM phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class TMPhone extends BasePhone
{
    /**
     * Wrap a raw TM phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'TM');
    }

    /**
     * Create an TMPhone from a raw number.
     */
    public static function make(string $number): TMPhone
    {
        return new self($number);
    }
}
