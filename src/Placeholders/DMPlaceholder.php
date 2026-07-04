<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\DMPhone;

/**
 * DM phone-number placeholder, mirroring {@see DMPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class DMPlaceholder extends BasePlaceholder
{
    /**
     * Build the DM placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('DM', $mask);
    }

    /**
     * Create a DMPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
