<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * QA phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class QAPhone extends BasePhone
{
    /**
     * Wrap a raw QA phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'QA');
    }

    /**
     * Create an QAPhone from a raw number.
     */
    public static function make(string $number): QAPhone
    {
        return new self($number);
    }
}
