<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\LIPhone;

/**
 * LI phone-number placeholder, mirroring {@see LIPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class LIPlaceholder extends BasePlaceholder
{
    /**
     * Build the LI placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('LI', $mask);
    }

    /**
     * Create a LIPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
