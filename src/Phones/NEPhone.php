<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * NE phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class NEPhone extends BasePhone
{
    /**
     * Wrap a raw NE phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'NE');
    }

    /**
     * Create an NEPhone from a raw number.
     */
    public static function make(string $number): NEPhone
    {
        return new self($number);
    }
}
