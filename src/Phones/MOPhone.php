<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MO phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MOPhone extends BasePhone
{
    /**
     * Wrap a raw MO phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MO');
    }

    /**
     * Create an MOPhone from a raw number.
     */
    public static function make(string $number): MOPhone
    {
        return new self($number);
    }
}
