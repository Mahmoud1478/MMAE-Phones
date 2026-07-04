<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * NA phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class NAPhone extends BasePhone
{
    /**
     * Wrap a raw NA phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'NA');
    }

    /**
     * Create an NAPhone from a raw number.
     */
    public static function make(string $number): NAPhone
    {
        return new self($number);
    }
}
