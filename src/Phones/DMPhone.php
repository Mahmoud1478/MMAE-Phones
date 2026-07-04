<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * DM phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class DMPhone extends BasePhone
{
    /**
     * Wrap a raw DM phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'DM');
    }

    /**
     * Create an DMPhone from a raw number.
     */
    public static function make(string $number): DMPhone
    {
        return new self($number);
    }
}
