<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SK phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SKPhone extends BasePhone
{
    /**
     * Wrap a raw SK phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SK');
    }

    /**
     * Create an SKPhone from a raw number.
     */
    public static function make(string $number): SKPhone
    {
        return new self($number);
    }
}
