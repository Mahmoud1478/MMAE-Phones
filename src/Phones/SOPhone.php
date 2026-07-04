<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SO phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SOPhone extends BasePhone
{
    /**
     * Wrap a raw SO phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SO');
    }

    /**
     * Create an SOPhone from a raw number.
     */
    public static function make(string $number): SOPhone
    {
        return new self($number);
    }
}
