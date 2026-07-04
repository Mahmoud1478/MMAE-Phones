<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\SIPhone;

/**
 * SI phone-number placeholder, mirroring {@see SIPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class SIPlaceholder extends BasePlaceholder
{
    /**
     * Build the SI placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('SI', $mask);
    }

    /**
     * Create a SIPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
