<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\COPhone;

/**
 * CO phone-number placeholder, mirroring {@see COPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class COPlaceholder extends BasePlaceholder
{
    /**
     * Build the CO placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('CO', $mask);
    }

    /**
     * Create a COPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
