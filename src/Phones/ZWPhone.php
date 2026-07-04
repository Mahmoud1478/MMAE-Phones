<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * ZW phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class ZWPhone extends BasePhone
{
    /**
     * Wrap a raw ZW phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'ZW');
    }

    /**
     * Create an ZWPhone from a raw number.
     */
    public static function make(string $number): ZWPhone
    {
        return new self($number);
    }
}
