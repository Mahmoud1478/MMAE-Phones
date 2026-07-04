<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * TW phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class TWPhone extends BasePhone
{
    /**
     * Wrap a raw TW phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'TW');
    }

    /**
     * Create an TWPhone from a raw number.
     */
    public static function make(string $number): TWPhone
    {
        return new self($number);
    }
}
