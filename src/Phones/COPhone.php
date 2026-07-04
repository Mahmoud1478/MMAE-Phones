<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * CO phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class COPhone extends BasePhone
{
    /**
     * Wrap a raw CO phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'CO');
    }

    /**
     * Create an COPhone from a raw number.
     */
    public static function make(string $number): COPhone
    {
        return new self($number);
    }
}
