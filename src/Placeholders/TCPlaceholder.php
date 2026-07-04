<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\TCPhone;

/**
 * TC phone-number placeholder, mirroring {@see TCPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class TCPlaceholder extends BasePlaceholder
{
    /**
     * Build the TC placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('TC', $mask);
    }

    /**
     * Create a TCPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
