<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * KM phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class KMPhone extends BasePhone
{
    /**
     * Wrap a raw KM phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'KM');
    }

    /**
     * Create an KMPhone from a raw number.
     */
    public static function make(string $number): KMPhone
    {
        return new self($number);
    }
}
