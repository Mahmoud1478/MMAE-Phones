<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * AI phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class AIPhone extends BasePhone
{
    /**
     * Wrap a raw AI phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'AI');
    }

    /**
     * Create an AIPhone from a raw number.
     */
    public static function make(string $number): AIPhone
    {
        return new self($number);
    }
}
