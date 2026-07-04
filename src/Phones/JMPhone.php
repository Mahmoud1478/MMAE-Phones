<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * JM phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class JMPhone extends BasePhone
{
    /**
     * Wrap a raw JM phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'JM');
    }

    /**
     * Create an JMPhone from a raw number.
     */
    public static function make(string $number): JMPhone
    {
        return new self($number);
    }
}
