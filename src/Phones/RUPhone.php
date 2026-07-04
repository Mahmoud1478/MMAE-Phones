<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * RU phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class RUPhone extends BasePhone
{
    /**
     * Wrap a raw RU phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'RU');
    }

    /**
     * Create an RUPhone from a raw number.
     */
    public static function make(string $number): RUPhone
    {
        return new self($number);
    }
}
