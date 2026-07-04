<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * VI phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class VIPhone extends BasePhone
{
    /**
     * Wrap a raw VI phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'VI');
    }

    /**
     * Create an VIPhone from a raw number.
     */
    public static function make(string $number): VIPhone
    {
        return new self($number);
    }
}
