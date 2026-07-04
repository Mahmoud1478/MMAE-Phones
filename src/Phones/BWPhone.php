<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BW phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BWPhone extends BasePhone
{
    /**
     * Wrap a raw BW phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BW');
    }

    /**
     * Create an BWPhone from a raw number.
     */
    public static function make(string $number): BWPhone
    {
        return new self($number);
    }
}
