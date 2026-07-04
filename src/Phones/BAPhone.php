<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * BA phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class BAPhone extends BasePhone
{
    /**
     * Wrap a raw BA phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'BA');
    }

    /**
     * Create an BAPhone from a raw number.
     */
    public static function make(string $number): BAPhone
    {
        return new self($number);
    }
}
