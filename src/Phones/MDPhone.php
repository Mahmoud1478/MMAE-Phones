<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MD phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MDPhone extends BasePhone
{
    /**
     * Wrap a raw MD phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MD');
    }

    /**
     * Create an MDPhone from a raw number.
     */
    public static function make(string $number): MDPhone
    {
        return new self($number);
    }
}
