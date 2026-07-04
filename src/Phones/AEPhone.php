<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * AE phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class AEPhone extends BasePhone
{
    /**
     * Wrap a raw AE phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'AE');
    }

    /**
     * Create an AEPhone from a raw number.
     */
    public static function make(string $number): AEPhone
    {
        return new self($number);
    }
}
