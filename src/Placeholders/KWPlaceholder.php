<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\KWPhone;

/**
 * KW phone-number placeholder, mirroring {@see KWPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class KWPlaceholder extends BasePlaceholder
{
    /**
     * Build the KW placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('KW', $mask);
    }

    /**
     * Create a KWPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
