<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * CG phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class CGPhone extends BasePhone
{
    /**
     * Wrap a raw CG phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'CG');
    }

    /**
     * Create an CGPhone from a raw number.
     */
    public static function make(string $number): CGPhone
    {
        return new self($number);
    }
}
