<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * IR phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class IRPhone extends BasePhone
{
    /**
     * Wrap a raw IR phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'IR');
    }

    /**
     * Create an IRPhone from a raw number.
     */
    public static function make(string $number): IRPhone
    {
        return new self($number);
    }
}
