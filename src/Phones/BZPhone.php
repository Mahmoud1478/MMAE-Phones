<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BZ phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BZPhone extends BasePhone
{
    /**
     * Wrap a raw BZ phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BZ');
    }

    /**
     * Create an BZPhone from a raw number.
     */
    public static function make(string $number): BZPhone
    {
        return new self($number);
    }
}
