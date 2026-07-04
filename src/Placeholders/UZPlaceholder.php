<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\UZPhone;

/**
 * UZ phone-number placeholder, mirroring {@see UZPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class UZPlaceholder extends BasePlaceholder
{
    /**
     * Build the UZ placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('UZ', $mask);
    }

    /**
     * Create a UZPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
