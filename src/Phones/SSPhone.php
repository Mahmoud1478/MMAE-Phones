<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SS phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SSPhone extends BasePhone
{
    /**
     * Wrap a raw SS phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SS');
    }

    /**
     * Create an SSPhone from a raw number.
     */
    public static function make(string $number): SSPhone
    {
        return new self($number);
    }
}
