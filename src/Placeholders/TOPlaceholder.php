<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\TOPhone;

/**
 * TO phone-number placeholder, mirroring {@see TOPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class TOPlaceholder extends BasePlaceholder
{
    /**
     * Build the TO placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('TO', $mask);
    }

    /**
     * Create a TOPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
