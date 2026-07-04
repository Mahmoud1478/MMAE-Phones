<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MG phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MGPhone extends BasePhone
{
    /**
     * Wrap a raw MG phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MG');
    }

    /**
     * Create an MGPhone from a raw number.
     */
    public static function make(string $number): MGPhone
    {
        return new self($number);
    }
}
