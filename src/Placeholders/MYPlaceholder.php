<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\MYPhone;

/**
 * MY phone-number placeholder, mirroring {@see MYPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class MYPlaceholder extends BasePlaceholder
{
    /**
     * Build the MY placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('MY', $mask);
    }

    /**
     * Create a MYPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
