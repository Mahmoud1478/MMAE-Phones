<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\DOPhone;

/**
 * DO phone-number placeholder, mirroring {@see DOPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class DOPlaceholder extends BasePlaceholder
{
    /**
     * Build the DO placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('DO', $mask);
    }

    /**
     * Create a DOPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
