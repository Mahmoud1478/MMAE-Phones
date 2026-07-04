<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * CV phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class CVPhone extends BasePhone
{
    /**
     * Wrap a raw CV phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'CV');
    }

    /**
     * Create an CVPhone from a raw number.
     */
    public static function make(string $number): CVPhone
    {
        return new self($number);
    }
}
