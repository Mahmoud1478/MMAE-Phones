<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SB phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SBPhone extends BasePhone
{
    /**
     * Wrap a raw SB phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SB');
    }

    /**
     * Create an SBPhone from a raw number.
     */
    public static function make(string $number): SBPhone
    {
        return new self($number);
    }
}
