<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MS phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MSPhone extends BasePhone
{
    /**
     * Wrap a raw MS phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MS');
    }

    /**
     * Create an MSPhone from a raw number.
     */
    public static function make(string $number): MSPhone
    {
        return new self($number);
    }
}
