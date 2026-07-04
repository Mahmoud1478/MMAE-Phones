<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * YE phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class YEPhone extends BasePhone
{
    /**
     * Wrap a raw YE phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'YE');
    }

    /**
     * Create an YEPhone from a raw number.
     */
    public static function make(string $number): YEPhone
    {
        return new self($number);
    }
}
