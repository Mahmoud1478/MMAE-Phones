<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BR phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BRPhone extends BasePhone
{
    /**
     * Wrap a raw BR phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BR');
    }

    /**
     * Create an BRPhone from a raw number.
     */
    public static function make(string $number): BRPhone
    {
        return new self($number);
    }
}
