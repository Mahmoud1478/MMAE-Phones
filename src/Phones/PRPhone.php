<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * PR phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class PRPhone extends BasePhone
{
    /**
     * Wrap a raw PR phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'PR');
    }

    /**
     * Create an PRPhone from a raw number.
     */
    public static function make(string $number): PRPhone
    {
        return new self($number);
    }
}
