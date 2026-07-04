<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * TZ phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class TZPhone extends BasePhone
{
    /**
     * Wrap a raw TZ phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'TZ');
    }

    /**
     * Create an TZPhone from a raw number.
     */
    public static function make(string $number): TZPhone
    {
        return new self($number);
    }
}
