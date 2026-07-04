<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\TMPhone;

/**
 * TM phone-number placeholder, mirroring {@see TMPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class TMPlaceholder extends BasePlaceholder
{
    /**
     * Build the TM placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('TM', $mask);
    }

    /**
     * Create a TMPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
