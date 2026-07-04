<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * CR phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class CRPhone extends BasePhone
{
    /**
     * Wrap a raw CR phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'CR');
    }

    /**
     * Create an CRPhone from a raw number.
     */
    public static function make(string $number): CRPhone
    {
        return new self($number);
    }
}
