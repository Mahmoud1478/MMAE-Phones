<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\BYPhone;

/**
 * BY phone-number placeholder, mirroring {@see BYPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class BYPlaceholder extends BasePlaceholder
{
    /**
     * Build the BY placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('BY', $mask);
    }

    /**
     * Create a BYPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
