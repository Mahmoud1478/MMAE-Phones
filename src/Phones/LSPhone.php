<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * LS phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class LSPhone extends BasePhone
{
    /**
     * Wrap a raw LS phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'LS');
    }

    /**
     * Create an LSPhone from a raw number.
     */
    public static function make(string $number): LSPhone
    {
        return new self($number);
    }
}
