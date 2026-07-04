<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * KP phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class KPPhone extends BasePhone
{
    /**
     * Wrap a raw KP phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'KP');
    }

    /**
     * Create an KPPhone from a raw number.
     */
    public static function make(string $number): KPPhone
    {
        return new self($number);
    }
}
