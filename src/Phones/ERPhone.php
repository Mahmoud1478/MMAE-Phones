<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * ER phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class ERPhone extends BasePhone
{
    /**
     * Wrap a raw ER phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'ER');
    }

    /**
     * Create an ERPhone from a raw number.
     */
    public static function make(string $number): ERPhone
    {
        return new self($number);
    }
}
