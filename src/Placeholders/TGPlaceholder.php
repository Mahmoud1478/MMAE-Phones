<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\TGPhone;

/**
 * TG phone-number placeholder, mirroring {@see TGPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class TGPlaceholder extends BasePlaceholder
{
    /**
     * Build the TG placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('TG', $mask);
    }

    /**
     * Create a TGPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
