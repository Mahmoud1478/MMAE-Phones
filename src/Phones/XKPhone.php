<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * XK phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class XKPhone extends BasePhone
{
    /**
     * Wrap a raw XK phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'XK');
    }

    /**
     * Create an XKPhone from a raw number.
     */
    public static function make(string $number): XKPhone
    {
        return new self($number);
    }
}
