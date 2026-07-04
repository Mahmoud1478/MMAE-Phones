<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * SD phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class SDPhone extends BasePhone
{
    /**
     * Wrap a raw SD phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'SD');
    }

    /**
     * Create an SDPhone from a raw number.
     */
    public static function make(string $number): SDPhone
    {
        return new self($number);
    }
}
