<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\EGPhone;

/**
 * EG phone-number placeholder, mirroring {@see EGPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class EGPlaceholder extends BasePlaceholder
{
    /**
     * Build the EG placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('EG', $mask);
    }

    /**
     * Create a EGPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
