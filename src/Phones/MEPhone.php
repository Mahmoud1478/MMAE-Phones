<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * ME phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MEPhone extends BasePhone
{
    /**
     * Wrap a raw ME phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'ME');
    }

    /**
     * Create an MEPhone from a raw number.
     */
    public static function make(string $number): MEPhone
    {
        return new self($number);
    }
}
