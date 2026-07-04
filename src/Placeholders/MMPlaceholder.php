<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\MMPhone;

/**
 * MM phone-number placeholder, mirroring {@see MMPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class MMPlaceholder extends BasePlaceholder
{
    /**
     * Build the MM placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('MM', $mask);
    }

    /**
     * Create a MMPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
