<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MR phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MRPhone extends BasePhone
{
    /**
     * Wrap a raw MR phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MR');
    }

    /**
     * Create an MRPhone from a raw number.
     */
    public static function make(string $number): MRPhone
    {
        return new self($number);
    }
}
