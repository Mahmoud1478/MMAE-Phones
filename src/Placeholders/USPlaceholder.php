<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\USPhone;

/**
 * US phone-number placeholder, mirroring {@see USPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class USPlaceholder extends BasePlaceholder
{
    /**
     * Build the US placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('US', $mask);
    }

    /**
     * Create a USPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
