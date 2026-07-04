<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * CZ phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class CZPhone extends BasePhone
{
    /**
     * Wrap a raw CZ phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'CZ');
    }

    /**
     * Create an CZPhone from a raw number.
     */
    public static function make(string $number): CZPhone
    {
        return new self($number);
    }
}
