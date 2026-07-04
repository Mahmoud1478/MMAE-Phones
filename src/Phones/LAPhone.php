<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * LA phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class LAPhone extends BasePhone
{
    /**
     * Wrap a raw LA phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'LA');
    }

    /**
     * Create an LAPhone from a raw number.
     */
    public static function make(string $number): LAPhone
    {
        return new self($number);
    }
}
