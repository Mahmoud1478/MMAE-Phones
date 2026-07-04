<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * PA phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class PAPhone extends BasePhone
{
    /**
     * Wrap a raw PA phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'PA');
    }

    /**
     * Create an PAPhone from a raw number.
     */
    public static function make(string $number): PAPhone
    {
        return new self($number);
    }
}
