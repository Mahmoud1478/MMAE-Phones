<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\CIPhone;

/**
 * CI phone-number placeholder, mirroring {@see CIPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class CIPlaceholder extends BasePlaceholder
{
    /**
     * Build the CI placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('CI', $mask);
    }

    /**
     * Create a CIPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
