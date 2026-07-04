<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SA phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SAPhone extends BasePhone
{
    /**
     * Wrap a raw SA phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SA');
    }

    /**
     * Create an SAPhone from a raw number.
     */
    public static function make(string $number): SAPhone
    {
        return new self($number);
    }
}
