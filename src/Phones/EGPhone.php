<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * EG phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class EGPhone extends BasePhone
{
    /**
     * Wrap a raw EG phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'EG');
    }

    /**
     * Create an EGPhone from a raw number.
     */
    public static function make(string $number): EGPhone
    {
        return new self($number);
    }
}
