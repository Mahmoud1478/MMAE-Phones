<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * CM phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class CMPhone extends BasePhone
{
    /**
     * Wrap a raw CM phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'CM');
    }

    /**
     * Create an CMPhone from a raw number.
     */
    public static function make(string $number): CMPhone
    {
        return new self($number);
    }
}
