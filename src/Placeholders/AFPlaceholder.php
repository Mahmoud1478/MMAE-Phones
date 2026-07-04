<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\AFPhone;

/**
 * AF phone-number placeholder, mirroring {@see AFPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class AFPlaceholder extends BasePlaceholder
{
    /**
     * Build the AF placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('AF', $mask);
    }

    /**
     * Create a AFPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
