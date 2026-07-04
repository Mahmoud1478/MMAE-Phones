<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * IN phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class INPhone extends BasePhone
{
    /**
     * Wrap a raw IN phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'IN');
    }

    /**
     * Create an INPhone from a raw number.
     */
    public static function make(string $number): INPhone
    {
        return new self($number);
    }
}
