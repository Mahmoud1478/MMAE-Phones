<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\GLPhone;

/**
 * GL phone-number placeholder, mirroring {@see GLPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class GLPlaceholder extends BasePlaceholder
{
    /**
     * Build the GL placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('GL', $mask);
    }

    /**
     * Create a GLPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
