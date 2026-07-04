<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\GDPhone;

/**
 * GD phone-number placeholder, mirroring {@see GDPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class GDPlaceholder extends BasePlaceholder
{
    /**
     * Build the GD placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('GD', $mask);
    }

    /**
     * Create a GDPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
