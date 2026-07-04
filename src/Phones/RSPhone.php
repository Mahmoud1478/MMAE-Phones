<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * RS phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class RSPhone extends BasePhone
{
    /**
     * Wrap a raw RS phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'RS');
    }

    /**
     * Create an RSPhone from a raw number.
     */
    public static function make(string $number): RSPhone
    {
        return new self($number);
    }
}
