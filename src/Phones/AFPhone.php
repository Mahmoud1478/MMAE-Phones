<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * AF phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class AFPhone extends BasePhone
{
    /**
     * Wrap a raw AF phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'AF');
    }

    /**
     * Create an AFPhone from a raw number.
     */
    public static function make(string $number): AFPhone
    {
        return new self($number);
    }
}
