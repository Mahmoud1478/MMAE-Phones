<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * GR phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class GRPhone extends BasePhone
{
    /**
     * Wrap a raw GR phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'GR');
    }

    /**
     * Create an GRPhone from a raw number.
     */
    public static function make(string $number): GRPhone
    {
        return new self($number);
    }
}
