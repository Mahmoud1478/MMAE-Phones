<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\LYPhone;

/**
 * LY phone-number placeholder, mirroring {@see LYPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class LYPlaceholder extends BasePlaceholder
{
    /**
     * Build the LY placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('LY', $mask);
    }

    /**
     * Create a LYPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
