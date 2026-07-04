<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SE phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SEPhone extends BasePhone
{
    /**
     * Wrap a raw SE phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SE');
    }

    /**
     * Create an SEPhone from a raw number.
     */
    public static function make(string $number): SEPhone
    {
        return new self($number);
    }
}
