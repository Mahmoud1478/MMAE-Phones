<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * JO phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class JOPhone extends BasePhone
{
    /**
     * Wrap a raw JO phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'JO');
    }

    /**
     * Create an JOPhone from a raw number.
     */
    public static function make(string $number): JOPhone
    {
        return new self($number);
    }
}
