<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\BZPhone;

/**
 * BZ phone-number placeholder, mirroring {@see BZPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class BZPlaceholder extends BasePlaceholder
{
    /**
     * Build the BZ placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('BZ', $mask);
    }

    /**
     * Create a BZPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
