<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * IL phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class ILPhone extends BasePhone
{
    /**
     * Wrap a raw IL phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'IL');
    }

    /**
     * Create an ILPhone from a raw number.
     */
    public static function make(string $number): ILPhone
    {
        return new self($number);
    }
}
