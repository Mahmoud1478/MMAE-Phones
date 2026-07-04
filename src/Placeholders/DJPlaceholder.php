<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\DJPhone;

/**
 * DJ phone-number placeholder, mirroring {@see DJPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class DJPlaceholder extends BasePlaceholder
{
    /**
     * Build the DJ placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('DJ', $mask);
    }

    /**
     * Create a DJPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
