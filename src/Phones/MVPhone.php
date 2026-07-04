<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * MV phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MVPhone extends BasePhone
{
    /**
     * Wrap a raw MV phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'MV');
    }

    /**
     * Create an MVPhone from a raw number.
     */
    public static function make(string $number): MVPhone
    {
        return new self($number);
    }
}
