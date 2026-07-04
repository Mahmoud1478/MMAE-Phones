<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MC phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MCPhone extends BasePhone
{
    /**
     * Wrap a raw MC phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MC');
    }

    /**
     * Create an MCPhone from a raw number.
     */
    public static function make(string $number): MCPhone
    {
        return new self($number);
    }
}
