<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\CLPhone;

/**
 * CL phone-number placeholder, mirroring {@see CLPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class CLPlaceholder extends BasePlaceholder
{
    /**
     * Build the CL placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('CL', $mask);
    }

    /**
     * Create a CLPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
