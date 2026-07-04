<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\ARPhone;

/**
 * AR phone-number placeholder, mirroring {@see ARPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class ARPlaceholder extends BasePlaceholder
{
    /**
     * Build the AR placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('AR', $mask);
    }

    /**
     * Create a ARPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
