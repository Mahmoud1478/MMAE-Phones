<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * PS phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class PSPhone extends BasePhone
{
    /**
     * Wrap a raw PS phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'PS');
    }

    /**
     * Create an PSPhone from a raw number.
     */
    public static function make(string $number): PSPhone
    {
        return new self($number);
    }
}
