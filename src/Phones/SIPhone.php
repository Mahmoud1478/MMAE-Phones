<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SI phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SIPhone extends BasePhone
{
    /**
     * Wrap a raw SI phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SI');
    }

    /**
     * Create an SIPhone from a raw number.
     */
    public static function make(string $number): SIPhone
    {
        return new self($number);
    }
}
