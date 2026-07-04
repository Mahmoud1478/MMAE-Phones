<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * AS phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class ASPhone extends BasePhone
{
    /**
     * Wrap a raw AS phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'AS');
    }

    /**
     * Create an ASPhone from a raw number.
     */
    public static function make(string $number): ASPhone
    {
        return new self($number);
    }
}
