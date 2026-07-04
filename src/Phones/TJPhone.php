<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * TJ phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class TJPhone extends BasePhone
{
    /**
     * Wrap a raw TJ phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'TJ');
    }

    /**
     * Create an TJPhone from a raw number.
     */
    public static function make(string $number): TJPhone
    {
        return new self($number);
    }
}
