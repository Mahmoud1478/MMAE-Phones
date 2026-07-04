<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * CI phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class CIPhone extends BasePhone
{
    /**
     * Wrap a raw CI phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'CI');
    }

    /**
     * Create an CIPhone from a raw number.
     */
    public static function make(string $number): CIPhone
    {
        return new self($number);
    }
}
