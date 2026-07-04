<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * ES phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class ESPhone extends BasePhone
{
    /**
     * Wrap a raw ES phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'ES');
    }

    /**
     * Create an ESPhone from a raw number.
     */
    public static function make(string $number): ESPhone
    {
        return new self($number);
    }
}
