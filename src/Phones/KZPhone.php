<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * KZ phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class KZPhone extends BasePhone
{
    /**
     * Wrap a raw KZ phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'KZ');
    }

    /**
     * Create an KZPhone from a raw number.
     */
    public static function make(string $number): KZPhone
    {
        return new self($number);
    }
}
