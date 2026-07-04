<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SC phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SCPhone extends BasePhone
{
    /**
     * Wrap a raw SC phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SC');
    }

    /**
     * Create an SCPhone from a raw number.
     */
    public static function make(string $number): SCPhone
    {
        return new self($number);
    }
}
