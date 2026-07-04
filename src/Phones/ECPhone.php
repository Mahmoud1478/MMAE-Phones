<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * EC phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class ECPhone extends BasePhone
{
    /**
     * Wrap a raw EC phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'EC');
    }

    /**
     * Create an ECPhone from a raw number.
     */
    public static function make(string $number): ECPhone
    {
        return new self($number);
    }
}
