<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\TNPhone;

/**
 * TN phone-number placeholder, mirroring {@see TNPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class TNPlaceholder extends BasePlaceholder
{
    /**
     * Build the TN placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('TN', $mask);
    }

    /**
     * Create a TNPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
