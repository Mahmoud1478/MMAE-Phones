<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * KW phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class KWPhone extends BasePhone
{
    /**
     * Wrap a raw KW phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'KW');
    }

    /**
     * Create an KWPhone from a raw number.
     */
    public static function make(string $number): KWPhone
    {
        return new self($number);
    }
}
