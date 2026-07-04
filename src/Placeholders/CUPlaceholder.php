<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\CUPhone;

/**
 * CU phone-number placeholder, mirroring {@see CUPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class CUPlaceholder extends BasePlaceholder
{
    /**
     * Build the CU placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('CU', $mask);
    }

    /**
     * Create a CUPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
