<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MU phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MUPhone extends BasePhone
{
    /**
     * Wrap a raw MU phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MU');
    }

    /**
     * Create an MUPhone from a raw number.
     */
    public static function make(string $number): MUPhone
    {
        return new self($number);
    }
}
