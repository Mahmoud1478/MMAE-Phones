<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BY phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BYPhone extends BasePhone
{
    /**
     * Wrap a raw BY phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BY');
    }

    /**
     * Create an BYPhone from a raw number.
     */
    public static function make(string $number): BYPhone
    {
        return new self($number);
    }
}
