<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BE phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BEPhone extends BasePhone
{
    /**
     * Wrap a raw BE phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BE');
    }

    /**
     * Create an BEPhone from a raw number.
     */
    public static function make(string $number): BEPhone
    {
        return new self($number);
    }
}
