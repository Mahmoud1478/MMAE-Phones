<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\INPhone;

/**
 * IN phone-number placeholder, mirroring {@see INPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class INPlaceholder extends BasePlaceholder
{
    /**
     * Build the IN placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('IN', $mask);
    }

    /**
     * Create a INPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
