<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * DE phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class DEPhone extends BasePhone
{
    /**
     * Wrap a raw DE phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'DE');
    }

    /**
     * Create an DEPhone from a raw number.
     */
    public static function make(string $number): DEPhone
    {
        return new self($number);
    }
}
