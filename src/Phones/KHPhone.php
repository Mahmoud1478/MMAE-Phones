<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * KH phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class KHPhone extends BasePhone
{
    /**
     * Wrap a raw KH phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'KH');
    }

    /**
     * Create an KHPhone from a raw number.
     */
    public static function make(string $number): KHPhone
    {
        return new self($number);
    }
}
