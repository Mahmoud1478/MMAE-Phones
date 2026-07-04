<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * FJ phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class FJPhone extends BasePhone
{
    /**
     * Wrap a raw FJ phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'FJ');
    }

    /**
     * Create an FJPhone from a raw number.
     */
    public static function make(string $number): FJPhone
    {
        return new self($number);
    }
}
