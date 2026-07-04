<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * TT phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class TTPhone extends BasePhone
{
    /**
     * Wrap a raw TT phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'TT');
    }

    /**
     * Create an TTPhone from a raw number.
     */
    public static function make(string $number): TTPhone
    {
        return new self($number);
    }
}
