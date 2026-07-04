<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * PF phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class PFPhone extends BasePhone
{
    /**
     * Wrap a raw PF phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'PF');
    }

    /**
     * Create an PFPhone from a raw number.
     */
    public static function make(string $number): PFPhone
    {
        return new self($number);
    }
}
