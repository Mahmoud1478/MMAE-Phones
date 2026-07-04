<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * GN phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class GNPhone extends BasePhone
{
    /**
     * Wrap a raw GN phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'GN');
    }

    /**
     * Create an GNPhone from a raw number.
     */
    public static function make(string $number): GNPhone
    {
        return new self($number);
    }
}
