<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\SYPhone;

/**
 * SY phone-number placeholder, mirroring {@see SYPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class SYPlaceholder extends BasePlaceholder
{
    /**
     * Build the SY placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('SY', $mask);
    }

    /**
     * Create a SYPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
