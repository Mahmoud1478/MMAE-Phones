<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * TD phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class TDPhone extends BasePhone
{
    /**
     * Wrap a raw TD phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'TD');
    }

    /**
     * Create an TDPhone from a raw number.
     */
    public static function make(string $number): TDPhone
    {
        return new self($number);
    }
}
