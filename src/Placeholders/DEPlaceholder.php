<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\DEPhone;

/**
 * DE phone-number placeholder, mirroring {@see DEPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class DEPlaceholder extends BasePlaceholder
{
    /**
     * Build the DE placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('DE', $mask);
    }

    /**
     * Create a DEPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
