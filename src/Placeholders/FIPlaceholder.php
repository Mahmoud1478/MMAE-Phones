<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\FIPhone;

/**
 * FI phone-number placeholder, mirroring {@see FIPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class FIPlaceholder extends BasePlaceholder
{
    /**
     * Build the FI placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('FI', $mask);
    }

    /**
     * Create a FIPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
