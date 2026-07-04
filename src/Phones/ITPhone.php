<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * IT phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class ITPhone extends BasePhone
{
    /**
     * Wrap a raw IT phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'IT');
    }

    /**
     * Create an ITPhone from a raw number.
     */
    public static function make(string $number): ITPhone
    {
        return new self($number);
    }
}
