<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * GH phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class GHPhone extends BasePhone
{
    /**
     * Wrap a raw GH phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'GH');
    }

    /**
     * Create an GHPhone from a raw number.
     */
    public static function make(string $number): GHPhone
    {
        return new self($number);
    }
}
