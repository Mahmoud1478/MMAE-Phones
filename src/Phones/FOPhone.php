<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * FO phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class FOPhone extends BasePhone
{
    /**
     * Wrap a raw FO phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'FO');
    }

    /**
     * Create an FOPhone from a raw number.
     */
    public static function make(string $number): FOPhone
    {
        return new self($number);
    }
}
