<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * GL phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class GLPhone extends BasePhone
{
    /**
     * Wrap a raw GL phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'GL');
    }

    /**
     * Create an GLPhone from a raw number.
     */
    public static function make(string $number): GLPhone
    {
        return new self($number);
    }
}
