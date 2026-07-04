<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * KG phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class KGPhone extends BasePhone
{
    /**
     * Wrap a raw KG phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'KG');
    }

    /**
     * Create an KGPhone from a raw number.
     */
    public static function make(string $number): KGPhone
    {
        return new self($number);
    }
}
