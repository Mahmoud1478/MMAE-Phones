<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\KGPhone;

/**
 * KG phone-number placeholder, mirroring {@see KGPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class KGPlaceholder extends BasePlaceholder
{
    /**
     * Build the KG placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('KG', $mask);
    }

    /**
     * Create a KGPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
