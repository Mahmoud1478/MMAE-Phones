<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MM phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MMPhone extends BasePhone
{
    /**
     * Wrap a raw MM phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MM');
    }

    /**
     * Create an MMPhone from a raw number.
     */
    public static function make(string $number): MMPhone
    {
        return new self($number);
    }
}
