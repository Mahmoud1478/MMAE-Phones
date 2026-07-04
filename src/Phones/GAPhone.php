<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * GA phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class GAPhone extends BasePhone
{
    /**
     * Wrap a raw GA phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'GA');
    }

    /**
     * Create an GAPhone from a raw number.
     */
    public static function make(string $number): GAPhone
    {
        return new self($number);
    }
}
