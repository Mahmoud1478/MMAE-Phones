<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * PY phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class PYPhone extends BasePhone
{
    /**
     * Wrap a raw PY phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'PY');
    }

    /**
     * Create an PYPhone from a raw number.
     */
    public static function make(string $number): PYPhone
    {
        return new self($number);
    }
}
