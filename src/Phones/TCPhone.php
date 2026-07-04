<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * TC phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class TCPhone extends BasePhone
{
    /**
     * Wrap a raw TC phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'TC');
    }

    /**
     * Create an TCPhone from a raw number.
     */
    public static function make(string $number): TCPhone
    {
        return new self($number);
    }
}
