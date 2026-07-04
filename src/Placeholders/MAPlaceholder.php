<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\MAPhone;

/**
 * MA phone-number placeholder, mirroring {@see MAPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class MAPlaceholder extends BasePlaceholder
{
    /**
     * Build the MA placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('MA', $mask);
    }

    /**
     * Create a MAPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
