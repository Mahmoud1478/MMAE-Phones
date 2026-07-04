<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BM phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BMPhone extends BasePhone
{
    /**
     * Wrap a raw BM phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BM');
    }

    /**
     * Create an BMPhone from a raw number.
     */
    public static function make(string $number): BMPhone
    {
        return new self($number);
    }
}
