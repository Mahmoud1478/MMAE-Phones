<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\VEPhone;

/**
 * VE phone-number placeholder, mirroring {@see VEPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class VEPlaceholder extends BasePlaceholder
{
    /**
     * Build the VE placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('VE', $mask);
    }

    /**
     * Create a VEPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
