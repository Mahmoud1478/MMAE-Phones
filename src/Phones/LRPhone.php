<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * LR phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class LRPhone extends BasePhone
{
    /**
     * Wrap a raw LR phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'LR');
    }

    /**
     * Create an LRPhone from a raw number.
     */
    public static function make(string $number): LRPhone
    {
        return new self($number);
    }
}
