<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * NP phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class NPPhone extends BasePhone
{
    /**
     * Wrap a raw NP phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'NP');
    }

    /**
     * Create an NPPhone from a raw number.
     */
    public static function make(string $number): NPPhone
    {
        return new self($number);
    }
}
