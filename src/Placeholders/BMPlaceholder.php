<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\BMPhone;

/**
 * BM phone-number placeholder, mirroring {@see BMPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class BMPlaceholder extends BasePlaceholder
{
    /**
     * Build the BM placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('BM', $mask);
    }

    /**
     * Create a BMPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
