<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SM phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SMPhone extends BasePhone
{
    /**
     * Wrap a raw SM phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SM');
    }

    /**
     * Create an SMPhone from a raw number.
     */
    public static function make(string $number): SMPhone
    {
        return new self($number);
    }
}
