<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SZ phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SZPhone extends BasePhone
{
    /**
     * Wrap a raw SZ phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SZ');
    }

    /**
     * Create an SZPhone from a raw number.
     */
    public static function make(string $number): SZPhone
    {
        return new self($number);
    }
}
