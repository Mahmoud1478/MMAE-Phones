<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * VC phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class VCPhone extends BasePhone
{
    /**
     * Wrap a raw VC phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'VC');
    }

    /**
     * Create an VCPhone from a raw number.
     */
    public static function make(string $number): VCPhone
    {
        return new self($number);
    }
}
