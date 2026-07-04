<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\PYPhone;

/**
 * PY phone-number placeholder, mirroring {@see PYPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class PYPlaceholder extends BasePlaceholder
{
    /**
     * Build the PY placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('PY', $mask);
    }

    /**
     * Create a PYPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
