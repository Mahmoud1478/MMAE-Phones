<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\IQPhone;

/**
 * IQ phone-number placeholder, mirroring {@see IQPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class IQPlaceholder extends BasePlaceholder
{
    /**
     * Build the IQ placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('IQ', $mask);
    }

    /**
     * Create a IQPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
