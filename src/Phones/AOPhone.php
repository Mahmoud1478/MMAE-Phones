<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * AO phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class AOPhone extends BasePhone
{
    /**
     * Wrap a raw AO phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'AO');
    }

    /**
     * Create an AOPhone from a raw number.
     */
    public static function make(string $number): AOPhone
    {
        return new self($number);
    }
}
