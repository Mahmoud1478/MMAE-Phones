<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * PT phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class PTPhone extends BasePhone
{
    /**
     * Wrap a raw PT phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'PT');
    }

    /**
     * Create an PTPhone from a raw number.
     */
    public static function make(string $number): PTPhone
    {
        return new self($number);
    }
}
