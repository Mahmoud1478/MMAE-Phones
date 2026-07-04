<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * TN phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class TNPhone extends BasePhone
{
    /**
     * Wrap a raw TN phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'TN');
    }

    /**
     * Create an TNPhone from a raw number.
     */
    public static function make(string $number): TNPhone
    {
        return new self($number);
    }
}
