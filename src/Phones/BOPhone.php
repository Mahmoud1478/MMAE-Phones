<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BO phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BOPhone extends BasePhone
{
    /**
     * Wrap a raw BO phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BO');
    }

    /**
     * Create an BOPhone from a raw number.
     */
    public static function make(string $number): BOPhone
    {
        return new self($number);
    }
}
