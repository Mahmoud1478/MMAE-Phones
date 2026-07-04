<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * GE phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class GEPhone extends BasePhone
{
    /**
     * Wrap a raw GE phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'GE');
    }

    /**
     * Create an GEPhone from a raw number.
     */
    public static function make(string $number): GEPhone
    {
        return new self($number);
    }
}
