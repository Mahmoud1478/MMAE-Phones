<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * CF phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class CFPhone extends BasePhone
{
    /**
     * Wrap a raw CF phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'CF');
    }

    /**
     * Create an CFPhone from a raw number.
     */
    public static function make(string $number): CFPhone
    {
        return new self($number);
    }
}
