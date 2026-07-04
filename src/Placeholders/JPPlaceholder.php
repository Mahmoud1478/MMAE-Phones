<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\JPPhone;

/**
 * JP phone-number placeholder, mirroring {@see JPPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class JPPlaceholder extends BasePlaceholder
{
    /**
     * Build the JP placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('JP', $mask);
    }

    /**
     * Create a JPPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
