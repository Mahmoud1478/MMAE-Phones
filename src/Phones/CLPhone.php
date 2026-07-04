<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * CL phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class CLPhone extends BasePhone
{
    /**
     * Wrap a raw CL phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'CL');
    }

    /**
     * Create an CLPhone from a raw number.
     */
    public static function make(string $number): CLPhone
    {
        return new self($number);
    }
}
