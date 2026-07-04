<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SG phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SGPhone extends BasePhone
{
    /**
     * Wrap a raw SG phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SG');
    }

    /**
     * Create an SGPhone from a raw number.
     */
    public static function make(string $number): SGPhone
    {
        return new self($number);
    }
}
