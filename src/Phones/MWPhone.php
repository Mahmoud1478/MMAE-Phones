<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MW phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MWPhone extends BasePhone
{
    /**
     * Wrap a raw MW phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MW');
    }

    /**
     * Create an MWPhone from a raw number.
     */
    public static function make(string $number): MWPhone
    {
        return new self($number);
    }
}
