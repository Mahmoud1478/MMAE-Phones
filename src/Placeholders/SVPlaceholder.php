<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\SVPhone;

/**
 * SV phone-number placeholder, mirroring {@see SVPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class SVPlaceholder extends BasePlaceholder
{
    /**
     * Build the SV placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('SV', $mask);
    }

    /**
     * Create a SVPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
