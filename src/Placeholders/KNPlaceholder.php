<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\KNPhone;

/**
 * KN phone-number placeholder, mirroring {@see KNPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class KNPlaceholder extends BasePlaceholder
{
    /**
     * Build the KN placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('KN', $mask);
    }

    /**
     * Create a KNPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
