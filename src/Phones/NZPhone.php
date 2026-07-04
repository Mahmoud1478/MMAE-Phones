<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * NZ phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class NZPhone extends BasePhone
{
    /**
     * Wrap a raw NZ phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'NZ');
    }

    /**
     * Create an NZPhone from a raw number.
     */
    public static function make(string $number): NZPhone
    {
        return new self($number);
    }
}
