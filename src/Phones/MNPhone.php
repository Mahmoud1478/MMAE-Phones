<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MN phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MNPhone extends BasePhone
{
    /**
     * Wrap a raw MN phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MN');
    }

    /**
     * Create an MNPhone from a raw number.
     */
    public static function make(string $number): MNPhone
    {
        return new self($number);
    }
}
