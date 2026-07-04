<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\LUPhone;

/**
 * LU phone-number placeholder, mirroring {@see LUPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class LUPlaceholder extends BasePlaceholder
{
    /**
     * Build the LU placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('LU', $mask);
    }

    /**
     * Create a LUPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
