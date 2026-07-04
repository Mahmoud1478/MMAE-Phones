<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SL phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SLPhone extends BasePhone
{
    /**
     * Wrap a raw SL phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SL');
    }

    /**
     * Create an SLPhone from a raw number.
     */
    public static function make(string $number): SLPhone
    {
        return new self($number);
    }
}
