<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\ERPhone;

/**
 * ER phone-number placeholder, mirroring {@see ERPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class ERPlaceholder extends BasePlaceholder
{
    /**
     * Build the ER placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('ER', $mask);
    }

    /**
     * Create a ERPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
