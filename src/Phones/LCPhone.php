<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * LC phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class LCPhone extends BasePhone
{
    /**
     * Wrap a raw LC phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'LC');
    }

    /**
     * Create an LCPhone from a raw number.
     */
    public static function make(string $number): LCPhone
    {
        return new self($number);
    }
}
