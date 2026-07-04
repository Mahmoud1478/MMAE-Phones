<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * IE phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class IEPhone extends BasePhone
{
    /**
     * Wrap a raw IE phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'IE');
    }

    /**
     * Create an IEPhone from a raw number.
     */
    public static function make(string $number): IEPhone
    {
        return new self($number);
    }
}
