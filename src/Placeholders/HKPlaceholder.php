<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\HKPhone;

/**
 * HK phone-number placeholder, mirroring {@see HKPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class HKPlaceholder extends BasePlaceholder
{
    /**
     * Build the HK placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('HK', $mask);
    }

    /**
     * Create a HKPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
