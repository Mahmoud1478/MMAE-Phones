<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * AZ phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class AZPhone extends BasePhone
{
    /**
     * Wrap a raw AZ phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'AZ');
    }

    /**
     * Create an AZPhone from a raw number.
     */
    public static function make(string $number): AZPhone
    {
        return new self($number);
    }
}
