<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * AM phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class AMPhone extends BasePhone
{
    /**
     * Wrap a raw AM phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'AM');
    }

    /**
     * Create an AMPhone from a raw number.
     */
    public static function make(string $number): AMPhone
    {
        return new self($number);
    }
}
