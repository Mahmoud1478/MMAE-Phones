<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\MUPhone;

/**
 * MU phone-number placeholder, mirroring {@see MUPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class MUPlaceholder extends BasePlaceholder
{
    /**
     * Build the MU placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('MU', $mask);
    }

    /**
     * Create a MUPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
