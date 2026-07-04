<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * NC phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class NCPhone extends BasePhone
{
    /**
     * Wrap a raw NC phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'NC');
    }

    /**
     * Create an NCPhone from a raw number.
     */
    public static function make(string $number): NCPhone
    {
        return new self($number);
    }
}
