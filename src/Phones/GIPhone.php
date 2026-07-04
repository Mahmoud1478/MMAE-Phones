<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * GI phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class GIPhone extends BasePhone
{
    /**
     * Wrap a raw GI phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'GI');
    }

    /**
     * Create an GIPhone from a raw number.
     */
    public static function make(string $number): GIPhone
    {
        return new self($number);
    }
}
