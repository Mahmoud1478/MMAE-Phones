<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\RWPhone;

/**
 * RW phone-number placeholder, mirroring {@see RWPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class RWPlaceholder extends BasePlaceholder
{
    /**
     * Build the RW placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('RW', $mask);
    }

    /**
     * Create a RWPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
