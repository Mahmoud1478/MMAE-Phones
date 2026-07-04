<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\EEPhone;

/**
 * EE phone-number placeholder, mirroring {@see EEPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class EEPlaceholder extends BasePlaceholder
{
    /**
     * Build the EE placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('EE', $mask);
    }

    /**
     * Create a EEPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
