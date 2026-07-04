<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\GTPhone;

/**
 * GT phone-number placeholder, mirroring {@see GTPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class GTPlaceholder extends BasePlaceholder
{
    /**
     * Build the GT placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('GT', $mask);
    }

    /**
     * Create a GTPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
