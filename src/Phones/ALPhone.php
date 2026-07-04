<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * AL phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class ALPhone extends BasePhone
{
    /**
     * Wrap a raw AL phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'AL');
    }

    /**
     * Create an ALPhone from a raw number.
     */
    public static function make(string $number): ALPhone
    {
        return new self($number);
    }
}
