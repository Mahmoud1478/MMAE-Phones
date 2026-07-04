<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * HT phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class HTPhone extends BasePhone
{
    /**
     * Wrap a raw HT phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'HT');
    }

    /**
     * Create an HTPhone from a raw number.
     */
    public static function make(string $number): HTPhone
    {
        return new self($number);
    }
}
