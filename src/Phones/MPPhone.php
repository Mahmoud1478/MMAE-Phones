<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MP phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MPPhone extends BasePhone
{
    /**
     * Wrap a raw MP phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MP');
    }

    /**
     * Create an MPPhone from a raw number.
     */
    public static function make(string $number): MPPhone
    {
        return new self($number);
    }
}
