<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\PFPhone;

/**
 * PF phone-number placeholder, mirroring {@see PFPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class PFPlaceholder extends BasePlaceholder
{
    /**
     * Build the PF placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('PF', $mask);
    }

    /**
     * Create a PFPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
