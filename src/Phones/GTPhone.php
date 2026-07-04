<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * GT phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class GTPhone extends BasePhone
{
    /**
     * Wrap a raw GT phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'GT');
    }

    /**
     * Create an GTPhone from a raw number.
     */
    public static function make(string $number): GTPhone
    {
        return new self($number);
    }
}
