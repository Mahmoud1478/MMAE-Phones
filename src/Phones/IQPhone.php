<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * IQ phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class IQPhone extends BasePhone
{
    /**
     * Wrap a raw IQ phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'IQ');
    }

    /**
     * Create an IQPhone from a raw number.
     */
    public static function make(string $number): IQPhone
    {
        return new self($number);
    }
}
