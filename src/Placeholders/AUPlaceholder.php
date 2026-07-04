<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\AUPhone;

/**
 * AU phone-number placeholder, mirroring {@see AUPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class AUPlaceholder extends BasePlaceholder
{
    /**
     * Build the AU placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('AU', $mask);
    }

    /**
     * Create a AUPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
