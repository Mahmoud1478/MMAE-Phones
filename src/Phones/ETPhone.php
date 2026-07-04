<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * ET phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class ETPhone extends BasePhone
{
    /**
     * Wrap a raw ET phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'ET');
    }

    /**
     * Create an ETPhone from a raw number.
     */
    public static function make(string $number): ETPhone
    {
        return new self($number);
    }
}
