<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * KY phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class KYPhone extends BasePhone
{
    /**
     * Wrap a raw KY phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'KY');
    }

    /**
     * Create an KYPhone from a raw number.
     */
    public static function make(string $number): KYPhone
    {
        return new self($number);
    }
}
