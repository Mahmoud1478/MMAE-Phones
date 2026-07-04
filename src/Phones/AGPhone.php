<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * AG phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class AGPhone extends BasePhone
{
    /**
     * Wrap a raw AG phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'AG');
    }

    /**
     * Create an AGPhone from a raw number.
     */
    public static function make(string $number): AGPhone
    {
        return new self($number);
    }
}
