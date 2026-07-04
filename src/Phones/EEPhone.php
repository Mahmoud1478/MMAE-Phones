<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * EE phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class EEPhone extends BasePhone
{
    /**
     * Wrap a raw EE phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'EE');
    }

    /**
     * Create an EEPhone from a raw number.
     */
    public static function make(string $number): EEPhone
    {
        return new self($number);
    }
}
