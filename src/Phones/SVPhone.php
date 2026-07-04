<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SV phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SVPhone extends BasePhone
{
    /**
     * Wrap a raw SV phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SV');
    }

    /**
     * Create an SVPhone from a raw number.
     */
    public static function make(string $number): SVPhone
    {
        return new self($number);
    }
}
