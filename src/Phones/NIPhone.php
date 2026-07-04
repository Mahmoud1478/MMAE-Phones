<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * NI phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class NIPhone extends BasePhone
{
    /**
     * Wrap a raw NI phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'NI');
    }

    /**
     * Create an NIPhone from a raw number.
     */
    public static function make(string $number): NIPhone
    {
        return new self($number);
    }
}
