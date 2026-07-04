<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * UZ phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class UZPhone extends BasePhone
{
    /**
     * Wrap a raw UZ phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'UZ');
    }

    /**
     * Create an UZPhone from a raw number.
     */
    public static function make(string $number): UZPhone
    {
        return new self($number);
    }
}
