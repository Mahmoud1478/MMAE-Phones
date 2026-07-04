<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\PRPhone;

/**
 * PR phone-number placeholder, mirroring {@see PRPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class PRPlaceholder extends BasePlaceholder
{
    /**
     * Build the PR placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('PR', $mask);
    }

    /**
     * Create a PRPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
