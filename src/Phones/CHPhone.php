<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * CH phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class CHPhone extends BasePhone
{
    /**
     * Wrap a raw CH phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'CH');
    }

    /**
     * Create an CHPhone from a raw number.
     */
    public static function make(string $number): CHPhone
    {
        return new self($number);
    }
}
