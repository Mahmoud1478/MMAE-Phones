<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * LI phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class LIPhone extends BasePhone
{
    /**
     * Wrap a raw LI phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'LI');
    }

    /**
     * Create an LIPhone from a raw number.
     */
    public static function make(string $number): LIPhone
    {
        return new self($number);
    }
}
