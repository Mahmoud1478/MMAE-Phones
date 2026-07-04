<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * TO phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class TOPhone extends BasePhone
{
    /**
     * Wrap a raw TO phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'TO');
    }

    /**
     * Create an TOPhone from a raw number.
     */
    public static function make(string $number): TOPhone
    {
        return new self($number);
    }
}
