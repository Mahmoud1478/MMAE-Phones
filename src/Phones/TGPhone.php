<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * TG phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class TGPhone extends BasePhone
{
    /**
     * Wrap a raw TG phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'TG');
    }

    /**
     * Create an TGPhone from a raw number.
     */
    public static function make(string $number): TGPhone
    {
        return new self($number);
    }
}
