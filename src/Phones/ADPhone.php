<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * AD phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class ADPhone extends BasePhone
{
    /**
     * Wrap a raw AD phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'AD');
    }

    /**
     * Create an ADPhone from a raw number.
     */
    public static function make(string $number): ADPhone
    {
        return new self($number);
    }
}
