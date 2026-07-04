<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * NL phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class NLPhone extends BasePhone
{
    /**
     * Wrap a raw NL phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'NL');
    }

    /**
     * Create an NLPhone from a raw number.
     */
    public static function make(string $number): NLPhone
    {
        return new self($number);
    }
}
