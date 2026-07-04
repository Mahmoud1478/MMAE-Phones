<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\SLPhone;

/**
 * SL phone-number placeholder, mirroring {@see SLPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class SLPlaceholder extends BasePlaceholder
{
    /**
     * Build the SL placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('SL', $mask);
    }

    /**
     * Create a SLPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
