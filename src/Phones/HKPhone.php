<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * HK phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class HKPhone extends BasePhone
{
    /**
     * Wrap a raw HK phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'HK');
    }

    /**
     * Create an HKPhone from a raw number.
     */
    public static function make(string $number): HKPhone
    {
        return new self($number);
    }
}
