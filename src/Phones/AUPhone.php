<?php

declare(strict_types=1);

namespace MMAE\Phones\Phones;

use MMAE\Phones\Base\BasePhone;

/**
 * AU phone number.
 *
 * @see BasePhone for the full API (isValid, toString, all, segments, withPlus, ...).
 */
final class AUPhone extends BasePhone
{
    /**
     * Wrap a raw AU phone number.
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'AU');
    }

    /**
     * Create an AUPhone from a raw number.
     */
    public static function make(string $number): AUPhone
    {
        return new self($number);
    }
}
