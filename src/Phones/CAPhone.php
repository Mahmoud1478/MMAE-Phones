<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * CA phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class CAPhone extends BasePhone
{
    /**
     * Wrap a raw CA phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'CA');
    }

    /**
     * Create an CAPhone from a raw number.
     */
    public static function make(string $number): CAPhone
    {
        return new self($number);
    }
}
