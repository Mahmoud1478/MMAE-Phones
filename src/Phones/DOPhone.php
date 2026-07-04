<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * DO phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class DOPhone extends BasePhone
{
    /**
     * Wrap a raw DO phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'DO');
    }

    /**
     * Create an DOPhone from a raw number.
     */
    public static function make(string $number): DOPhone
    {
        return new self($number);
    }
}
