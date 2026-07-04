<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MT phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MTPhone extends BasePhone
{
    /**
     * Wrap a raw MT phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MT');
    }

    /**
     * Create an MTPhone from a raw number.
     */
    public static function make(string $number): MTPhone
    {
        return new self($number);
    }
}
