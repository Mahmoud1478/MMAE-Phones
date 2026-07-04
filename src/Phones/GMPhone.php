<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * GM phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class GMPhone extends BasePhone
{
    /**
     * Wrap a raw GM phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'GM');
    }

    /**
     * Create an GMPhone from a raw number.
     */
    public static function make(string $number): GMPhone
    {
        return new self($number);
    }
}
