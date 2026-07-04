<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\BWPhone;

/**
 * BW phone-number placeholder, mirroring {@see BWPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class BWPlaceholder extends BasePlaceholder
{
    /**
     * Build the BW placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('BW', $mask);
    }

    /**
     * Create a BWPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
