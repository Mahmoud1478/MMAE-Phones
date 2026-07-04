<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\GRPhone;

/**
 * GR phone-number placeholder, mirroring {@see GRPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class GRPlaceholder extends BasePlaceholder
{
    /**
     * Build the GR placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('GR', $mask);
    }

    /**
     * Create a GRPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
