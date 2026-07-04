<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * DK phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class DKPhone extends BasePhone
{
    /**
     * Wrap a raw DK phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'DK');
    }

    /**
     * Create an DKPhone from a raw number.
     */
    public static function make(string $number): DKPhone
    {
        return new self($number);
    }
}
