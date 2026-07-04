<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * KN phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class KNPhone extends BasePhone
{
    /**
     * Wrap a raw KN phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'KN');
    }

    /**
     * Create an KNPhone from a raw number.
     */
    public static function make(string $number): KNPhone
    {
        return new self($number);
    }
}
