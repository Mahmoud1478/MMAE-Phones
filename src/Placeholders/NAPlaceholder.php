<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\NAPhone;

/**
 * NA phone-number placeholder, mirroring {@see NAPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class NAPlaceholder extends BasePlaceholder
{
    /**
     * Build the NA placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('NA', $mask);
    }

    /**
     * Create a NAPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
