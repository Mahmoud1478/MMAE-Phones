<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\STPhone;

/**
 * ST phone-number placeholder, mirroring {@see STPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class STPlaceholder extends BasePlaceholder
{
    /**
     * Build the ST placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('ST', $mask);
    }

    /**
     * Create a STPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
