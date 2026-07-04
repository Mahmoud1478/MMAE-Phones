<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * RW phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class RWPhone extends BasePhone
{
    /**
     * Wrap a raw RW phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'RW');
    }

    /**
     * Create an RWPhone from a raw number.
     */
    public static function make(string $number): RWPhone
    {
        return new self($number);
    }
}
