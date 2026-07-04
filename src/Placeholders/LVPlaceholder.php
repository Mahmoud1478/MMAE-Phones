<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\LVPhone;

/**
 * LV phone-number placeholder, mirroring {@see LVPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class LVPlaceholder extends BasePlaceholder
{
    /**
     * Build the LV placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('LV', $mask);
    }

    /**
     * Create a LVPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
