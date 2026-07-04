<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\AOPhone;

/**
 * AO phone-number placeholder, mirroring {@see AOPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class AOPlaceholder extends BasePlaceholder
{
    /**
     * Build the AO placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('AO', $mask);
    }

    /**
     * Create a AOPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
