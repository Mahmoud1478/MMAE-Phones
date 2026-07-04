<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\MDPhone;

/**
 * MD phone-number placeholder, mirroring {@see MDPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class MDPlaceholder extends BasePlaceholder
{
    /**
     * Build the MD placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('MD', $mask);
    }

    /**
     * Create a MDPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
