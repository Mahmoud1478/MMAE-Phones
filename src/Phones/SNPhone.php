<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SN phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SNPhone extends BasePhone
{
    /**
     * Wrap a raw SN phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SN');
    }

    /**
     * Create an SNPhone from a raw number.
     */
    public static function make(string $number): SNPhone
    {
        return new self($number);
    }
}
