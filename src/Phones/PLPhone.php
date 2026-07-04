<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * PL phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class PLPhone extends BasePhone
{
    /**
     * Wrap a raw PL phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'PL');
    }

    /**
     * Create an PLPhone from a raw number.
     */
    public static function make(string $number): PLPhone
    {
        return new self($number);
    }
}
