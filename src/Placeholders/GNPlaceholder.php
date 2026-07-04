<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\GNPhone;

/**
 * GN phone-number placeholder, mirroring {@see GNPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class GNPlaceholder extends BasePlaceholder
{
    /**
     * Build the GN placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('GN', $mask);
    }

    /**
     * Create a GNPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
