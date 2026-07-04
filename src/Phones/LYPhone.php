<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * LY phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class LYPhone extends BasePhone
{
    /**
     * Wrap a raw LY phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'LY');
    }

    /**
     * Create an LYPhone from a raw number.
     */
    public static function make(string $number): LYPhone
    {
        return new self($number);
    }
}
