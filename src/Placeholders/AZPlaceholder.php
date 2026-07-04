<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\AZPhone;

/**
 * AZ phone-number placeholder, mirroring {@see AZPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class AZPlaceholder extends BasePlaceholder
{
    /**
     * Build the AZ placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('AZ', $mask);
    }

    /**
     * Create a AZPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
