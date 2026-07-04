<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\PLPhone;

/**
 * PL phone-number placeholder, mirroring {@see PLPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class PLPlaceholder extends BasePlaceholder
{
    /**
     * Build the PL placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('PL', $mask);
    }

    /**
     * Create a PLPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
