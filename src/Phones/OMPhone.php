<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * OM phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class OMPhone extends BasePhone
{
    /**
     * Wrap a raw OM phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'OM');
    }

    /**
     * Create an OMPhone from a raw number.
     */
    public static function make(string $number): OMPhone
    {
        return new self($number);
    }
}
