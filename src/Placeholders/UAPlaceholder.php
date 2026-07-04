<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\UAPhone;

/**
 * UA phone-number placeholder, mirroring {@see UAPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class UAPlaceholder extends BasePlaceholder
{
    /**
     * Build the UA placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('UA', $mask);
    }

    /**
     * Create a UAPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
