<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * DZ phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class DZPhone extends BasePhone
{
    /**
     * Wrap a raw DZ phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'DZ');
    }

    /**
     * Create an DZPhone from a raw number.
     */
    public static function make(string $number): DZPhone
    {
        return new self($number);
    }
}
