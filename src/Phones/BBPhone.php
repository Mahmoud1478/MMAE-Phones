<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BB phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BBPhone extends BasePhone
{
    /**
     * Wrap a raw BB phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BB');
    }

    /**
     * Create an BBPhone from a raw number.
     */
    public static function make(string $number): BBPhone
    {
        return new self($number);
    }
}
