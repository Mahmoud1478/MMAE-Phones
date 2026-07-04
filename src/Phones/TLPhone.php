<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * TL phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class TLPhone extends BasePhone
{
    /**
     * Wrap a raw TL phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'TL');
    }

    /**
     * Create an TLPhone from a raw number.
     */
    public static function make(string $number): TLPhone
    {
        return new self($number);
    }
}
