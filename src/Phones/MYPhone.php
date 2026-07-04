<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MY phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MYPhone extends BasePhone
{
    /**
     * Wrap a raw MY phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MY');
    }

    /**
     * Create an MYPhone from a raw number.
     */
    public static function make(string $number): MYPhone
    {
        return new self($number);
    }
}
