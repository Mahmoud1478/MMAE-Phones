<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * UA phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class UAPhone extends BasePhone
{
    /**
     * Wrap a raw UA phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'UA');
    }

    /**
     * Create an UAPhone from a raw number.
     */
    public static function make(string $number): UAPhone
    {
        return new self($number);
    }
}
