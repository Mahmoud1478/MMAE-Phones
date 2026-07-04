<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MZ phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MZPhone extends BasePhone
{
    /**
     * Wrap a raw MZ phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MZ');
    }

    /**
     * Create an MZPhone from a raw number.
     */
    public static function make(string $number): MZPhone
    {
        return new self($number);
    }
}
