<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * HN phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class HNPhone extends BasePhone
{
    /**
     * Wrap a raw HN phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'HN');
    }

    /**
     * Create an HNPhone from a raw number.
     */
    public static function make(string $number): HNPhone
    {
        return new self($number);
    }
}
