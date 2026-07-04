<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * AT phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class ATPhone extends BasePhone
{
    /**
     * Wrap a raw AT phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'AT');
    }

    /**
     * Create an ATPhone from a raw number.
     */
    public static function make(string $number): ATPhone
    {
        return new self($number);
    }
}
