<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * US phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class USPhone extends BasePhone
{
    /**
     * Wrap a raw US phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'US');
    }

    /**
     * Create an USPhone from a raw number.
     */
    public static function make(string $number): USPhone
    {
        return new self($number);
    }
}
