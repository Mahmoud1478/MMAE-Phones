<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * NO phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class NOPhone extends BasePhone
{
    /**
     * Wrap a raw NO phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'NO');
    }

    /**
     * Create an NOPhone from a raw number.
     */
    public static function make(string $number): NOPhone
    {
        return new self($number);
    }
}
