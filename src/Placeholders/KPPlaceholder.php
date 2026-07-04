<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\KPPhone;

/**
 * KP phone-number placeholder, mirroring {@see KPPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class KPPlaceholder extends BasePlaceholder
{
    /**
     * Build the KP placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('KP', $mask);
    }

    /**
     * Create a KPPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
