<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * GD phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class GDPhone extends BasePhone
{
    /**
     * Wrap a raw GD phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'GD');
    }

    /**
     * Create an GDPhone from a raw number.
     */
    public static function make(string $number): GDPhone
    {
        return new self($number);
    }
}
