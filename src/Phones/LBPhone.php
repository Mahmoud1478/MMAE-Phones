<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * LB phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class LBPhone extends BasePhone
{
    /**
     * Wrap a raw LB phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'LB');
    }

    /**
     * Create an LBPhone from a raw number.
     */
    public static function make(string $number): LBPhone
    {
        return new self($number);
    }
}
