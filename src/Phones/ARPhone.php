<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * AR phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class ARPhone extends BasePhone
{
    /**
     * Wrap a raw AR phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'AR');
    }

    /**
     * Create an ARPhone from a raw number.
     */
    public static function make(string $number): ARPhone
    {
        return new self($number);
    }
}
