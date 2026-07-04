<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * JP phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class JPPhone extends BasePhone
{
    /**
     * Wrap a raw JP phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'JP');
    }

    /**
     * Create an JPPhone from a raw number.
     */
    public static function make(string $number): JPPhone
    {
        return new self($number);
    }
}
