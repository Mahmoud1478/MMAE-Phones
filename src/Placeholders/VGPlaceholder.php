<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\VGPhone;

/**
 * VG phone-number placeholder, mirroring {@see VGPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class VGPlaceholder extends BasePlaceholder
{
    /**
     * Build the VG placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('VG', $mask);
    }

    /**
     * Create a VGPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
