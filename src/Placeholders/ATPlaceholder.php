<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\ATPhone;

/**
 * AT phone-number placeholder, mirroring {@see ATPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class ATPlaceholder extends BasePlaceholder
{
    /**
     * Build the AT placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('AT', $mask);
    }

    /**
     * Create a ATPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
