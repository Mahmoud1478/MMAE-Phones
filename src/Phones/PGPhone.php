<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * PG phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class PGPhone extends BasePhone
{
    /**
     * Wrap a raw PG phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'PG');
    }

    /**
     * Create an PGPhone from a raw number.
     */
    public static function make(string $number): PGPhone
    {
        return new self($number);
    }
}
