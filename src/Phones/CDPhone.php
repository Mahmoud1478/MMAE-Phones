<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * CD phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class CDPhone extends BasePhone
{
    /**
     * Wrap a raw CD phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'CD');
    }

    /**
     * Create an CDPhone from a raw number.
     */
    public static function make(string $number): CDPhone
    {
        return new self($number);
    }
}
