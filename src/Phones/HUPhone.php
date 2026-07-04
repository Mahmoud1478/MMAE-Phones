<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * HU phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class HUPhone extends BasePhone
{
    /**
     * Wrap a raw HU phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'HU');
    }

    /**
     * Create an HUPhone from a raw number.
     */
    public static function make(string $number): HUPhone
    {
        return new self($number);
    }
}
