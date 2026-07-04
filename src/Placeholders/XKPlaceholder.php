<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\XKPhone;

/**
 * XK phone-number placeholder, mirroring {@see XKPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class XKPlaceholder extends BasePlaceholder
{
    /**
     * Build the XK placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('XK', $mask);
    }

    /**
     * Create a XKPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
