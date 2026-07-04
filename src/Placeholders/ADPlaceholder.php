<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\ADPhone;

/**
 * AD phone-number placeholder, mirroring {@see ADPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class ADPlaceholder extends BasePlaceholder
{
    /**
     * Build the AD placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('AD', $mask);
    }

    /**
     * Create a ADPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
