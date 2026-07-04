<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * PE phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class PEPhone extends BasePhone
{
    /**
     * Wrap a raw PE phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'PE');
    }

    /**
     * Create an PEPhone from a raw number.
     */
    public static function make(string $number): PEPhone
    {
        return new self($number);
    }
}
