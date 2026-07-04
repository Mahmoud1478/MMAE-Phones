<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * IS phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class ISPhone extends BasePhone
{
    /**
     * Wrap a raw IS phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'IS');
    }

    /**
     * Create an ISPhone from a raw number.
     */
    public static function make(string $number): ISPhone
    {
        return new self($number);
    }
}
