<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\PSPhone;

/**
 * PS phone-number placeholder, mirroring {@see PSPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class PSPlaceholder extends BasePlaceholder
{
    /**
     * Build the PS placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('PS', $mask);
    }

    /**
     * Create a PSPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
