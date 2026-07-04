<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * PK phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class PKPhone extends BasePhone
{
    /**
     * Wrap a raw PK phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'PK');
    }

    /**
     * Create an PKPhone from a raw number.
     */
    public static function make(string $number): PKPhone
    {
        return new self($number);
    }
}
