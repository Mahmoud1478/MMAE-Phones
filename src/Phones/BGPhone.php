<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BG phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BGPhone extends BasePhone
{
    /**
     * Wrap a raw BG phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BG');
    }

    /**
     * Create an BGPhone from a raw number.
     */
    public static function make(string $number): BGPhone
    {
        return new self($number);
    }
}
