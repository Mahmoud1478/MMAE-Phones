<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * KR phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class KRPhone extends BasePhone
{
    /**
     * Wrap a raw KR phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'KR');
    }

    /**
     * Create an KRPhone from a raw number.
     */
    public static function make(string $number): KRPhone
    {
        return new self($number);
    }
}
