<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BT phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BTPhone extends BasePhone
{
    /**
     * Wrap a raw BT phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BT');
    }

    /**
     * Create an BTPhone from a raw number.
     */
    public static function make(string $number): BTPhone
    {
        return new self($number);
    }
}
