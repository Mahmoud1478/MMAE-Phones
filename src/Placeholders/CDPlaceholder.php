<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\CDPhone;

/**
 * CD phone-number placeholder, mirroring {@see CDPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class CDPlaceholder extends BasePlaceholder
{
    /**
     * Build the CD placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('CD', $mask);
    }

    /**
     * Create a CDPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
