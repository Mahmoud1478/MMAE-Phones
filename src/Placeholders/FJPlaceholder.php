<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\FJPhone;

/**
 * FJ phone-number placeholder, mirroring {@see FJPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class FJPlaceholder extends BasePlaceholder
{
    /**
     * Build the FJ placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('FJ', $mask);
    }

    /**
     * Create a FJPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
