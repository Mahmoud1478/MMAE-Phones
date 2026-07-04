<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * LK phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class LKPhone extends BasePhone
{
    /**
     * Wrap a raw LK phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'LK');
    }

    /**
     * Create an LKPhone from a raw number.
     */
    public static function make(string $number): LKPhone
    {
        return new self($number);
    }
}
