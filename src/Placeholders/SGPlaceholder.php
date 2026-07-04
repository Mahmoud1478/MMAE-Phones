<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\SGPhone;

/**
 * SG phone-number placeholder, mirroring {@see SGPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class SGPlaceholder extends BasePlaceholder
{
    /**
     * Build the SG placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('SG', $mask);
    }

    /**
     * Create a SGPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
