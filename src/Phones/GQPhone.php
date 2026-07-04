<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * GQ phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class GQPhone extends BasePhone
{
    /**
     * Wrap a raw GQ phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'GQ');
    }

    /**
     * Create an GQPhone from a raw number.
     */
    public static function make(string $number): GQPhone
    {
        return new self($number);
    }
}
