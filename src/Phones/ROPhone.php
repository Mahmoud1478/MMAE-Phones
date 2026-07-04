<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * RO phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class ROPhone extends BasePhone
{
    /**
     * Wrap a raw RO phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'RO');
    }

    /**
     * Create an ROPhone from a raw number.
     */
    public static function make(string $number): ROPhone
    {
        return new self($number);
    }
}
