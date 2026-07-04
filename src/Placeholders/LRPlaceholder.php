<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\LRPhone;

/**
 * LR phone-number placeholder, mirroring {@see LRPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class LRPlaceholder extends BasePlaceholder
{
    /**
     * Build the LR placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('LR', $mask);
    }

    /**
     * Create a LRPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
