<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BJ phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BJPhone extends BasePhone
{
    /**
     * Wrap a raw BJ phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BJ');
    }

    /**
     * Create an BJPhone from a raw number.
     */
    public static function make(string $number): BJPhone
    {
        return new self($number);
    }
}
