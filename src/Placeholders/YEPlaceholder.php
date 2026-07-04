<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\YEPhone;

/**
 * YE phone-number placeholder, mirroring {@see YEPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class YEPlaceholder extends BasePlaceholder
{
    /**
     * Build the YE placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('YE', $mask);
    }

    /**
     * Create a YEPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
