<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\RSPhone;

/**
 * RS phone-number placeholder, mirroring {@see RSPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class RSPlaceholder extends BasePlaceholder
{
    /**
     * Build the RS placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('RS', $mask);
    }

    /**
     * Create a RSPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
