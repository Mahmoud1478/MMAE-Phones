<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * ML phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class MLPhone extends BasePhone
{
    /**
     * Wrap a raw ML phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'ML');
    }

    /**
     * Create an MLPhone from a raw number.
     */
    public static function make(string $number): MLPhone
    {
        return new self($number);
    }
}
