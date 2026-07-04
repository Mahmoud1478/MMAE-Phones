<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BS phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BSPhone extends BasePhone
{
    /**
     * Wrap a raw BS phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BS');
    }

    /**
     * Create an BSPhone from a raw number.
     */
    public static function make(string $number): BSPhone
    {
        return new self($number);
    }
}
