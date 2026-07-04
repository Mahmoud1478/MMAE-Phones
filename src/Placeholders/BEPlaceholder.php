<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\BEPhone;

/**
 * BE phone-number placeholder, mirroring {@see BEPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class BEPlaceholder extends BasePlaceholder
{
    /**
     * Build the BE placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('BE', $mask);
    }

    /**
     * Create a BEPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
