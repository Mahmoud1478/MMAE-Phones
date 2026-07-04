<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * TH phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class THPhone extends BasePhone
{
    /**
     * Wrap a raw TH phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'TH');
    }

    /**
     * Create an THPhone from a raw number.
     */
    public static function make(string $number): THPhone
    {
        return new self($number);
    }
}
