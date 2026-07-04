<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SR phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SRPhone extends BasePhone
{
    /**
     * Wrap a raw SR phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SR');
    }

    /**
     * Create an SRPhone from a raw number.
     */
    public static function make(string $number): SRPhone
    {
        return new self($number);
    }
}
