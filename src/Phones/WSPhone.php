<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * WS phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class WSPhone extends BasePhone
{
    /**
     * Wrap a raw WS phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'WS');
    }

    /**
     * Create an WSPhone from a raw number.
     */
    public static function make(string $number): WSPhone
    {
        return new self($number);
    }
}
