<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * ZA phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class ZAPhone extends BasePhone
{
    /**
     * Wrap a raw ZA phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'ZA');
    }

    /**
     * Create an ZAPhone from a raw number.
     */
    public static function make(string $number): ZAPhone
    {
        return new self($number);
    }
}
