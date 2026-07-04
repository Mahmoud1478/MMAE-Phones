<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * LV phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class LVPhone extends BasePhone
{
    /**
     * Wrap a raw LV phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'LV');
    }

    /**
     * Create an LVPhone from a raw number.
     */
    public static function make(string $number): LVPhone
    {
        return new self($number);
    }
}
